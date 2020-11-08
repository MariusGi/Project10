<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\DailyCountryChecksOnRequest;
use App\Entity\PublicHoliday;
use App\Form\PublicHolidayType;
use App\Repository\CountryRepository;
use App\Repository\DailyCountryChecksOnRequestRepository;
use App\Repository\PublicHolidayRepository;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Api\MakeApiRequest;

class PublicHolidaysController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/public-holidays", name="public_holidays")
     * @param Request $request
     * @param CountryRepository $countryRepository
     * @param PublicHolidayRepository $publicHolidayRepository
     * @param DailyCountryChecksOnRequestRepository $dailyCountryChecksOnRequestRepository
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, CountryRepository $countryRepository,
                          PublicHolidayRepository $publicHolidayRepository,
                          DailyCountryChecksOnRequestRepository $dailyCountryChecksOnRequestRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $makeApiRequest = new MakeApiRequest();
        $isCountryTableHasData = $countryRepository->findBy(array(), null, 1);

        ///////////////////////////////////////////////////////
        // Add countries in API to Country table if it is empty
        ///////////////////////////////////////////////////////
        if (!$isCountryTableHasData) {
            $content = $makeApiRequest->getSupportedCountries($this->client);

            foreach ($content as $countryData) {
                $country = new Country();
                $country->setCountryCode($countryData['countryCode']);
                $country->setFullName($countryData['fullName']);
                $country->setHolidaysAvailableFromYear($countryData['fromDate']['year']);
                $country->setHolidaysAvailableToYear($countryData['toDate']['year']);

                $entityManager->persist($country);
            }

            $entityManager->flush();
        }

        $form = $this->createForm(PublicHolidayType::class);
        $form->handleRequest($request);

        ///////////////////////////////////////////////////////
        // After form submit
        ///////////////////////////////////////////////////////
        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();

            ///////////////////////////////////////////////////////
            // Check if country status already exists in db for today
            ///////////////////////////////////////////////////////
            $currentDate = date('d-m-Y');

            if ($dailyCountryChecksData = $dailyCountryChecksOnRequestRepository->findOneBy([
                'country' => $formData['country']->getId(),
                'updated_on' => new DateTime($currentDate),
            ])) {
                $typeOfDay = $dailyCountryChecksData->getTypeOfDay();
            } else {
                $dailyCountryChecksOnRequest = new DailyCountryChecksOnRequest();
                $dailyCountryChecksOnRequest->setCountry($formData['country']);
                $countryData = $countryRepository->findOneBy(['id' => $formData['country']->getId()]);
                $countryCode = $countryData->getCountryCode();
                $typeOfDay = $makeApiRequest->getTypeOfDay($this->client, $countryCode);
                $dailyCountryChecksOnRequest->setTypeOfDay($typeOfDay);
                $dailyCountryChecksOnRequest->setUpdatedOn(new DateTime($currentDate));

                $entityManager->persist($dailyCountryChecksOnRequest);
                $entityManager->flush();
            }

            ///////////////////////////////////////////////////////
            // Check if holiday data already exists in db
            ///////////////////////////////////////////////////////
            if ($publicHolidayData = $publicHolidayRepository->findOneBy([
                'country' => $formData['country']->getId(),
                'year' => $formData['year'],
            ])) {

                $publicHolidaysMonthDay = $publicHolidayData->getMonthDay();
                $totalAmountOfPublicHolidays = $publicHolidayData->getTotalAmount();
                $maxFreeDaysInARow = $publicHolidayData->getMaxFreeDaysInARow();

                $publicHolidayFilterResult = [
                    'public_holidays' => $publicHolidaysMonthDay,
                    'total_amount_of_public_holidays' => $totalAmountOfPublicHolidays,
                    'status' => $typeOfDay,
                    'max_number_of_free_days' => $maxFreeDaysInARow,
                ];

                return $this->render('public_holidays/index.html.twig', [
                    'controller_name' => 'PublicHolidaysController',
                    'public_holiday_form' => $form->createView(),
                    'public_holiday_filter_result' => $publicHolidayFilterResult,
                ]);
            }

            $countryData = $countryRepository->findOneBy(['id' => $formData['country']->getId()]);
            $countryCode = $countryData->getCountryCode();
            $dataAvailableFromYear = $countryData->getHolidaysAvailableFromYear();
            $dataAvailableToYear = $countryData->getHolidaysAvailableToYear();

            ///////////////////////////////////////////////////////
            // Have necessary data to make api request
            ///////////////////////////////////////////////////////
            if ($formData['year'] >= $dataAvailableFromYear && $formData['year'] <= $dataAvailableToYear) {
                $content = $makeApiRequest->getPublicHolidaysForYear($this->client, $formData['year'], $countryCode);

                ///////////////////////////////////////////////////////
                // Insert data into public_holidays table
                ///////////////////////////////////////////////////////
                $publicHolidaysMonthDay = [];
                $monthMapping = [
                    1 => 'jan',
                    2 => 'feb',
                    3 => 'mar',
                    4 => 'apr',
                    5 => 'may',
                    6 => 'jun',
                    7 => 'jul',
                    8 => 'aug',
                    9 => 'sep',
                    10 => 'oct',
                    11 => 'nov',
                    12 => 'dec',
                ];
                $totalAmountOfPublicHolidays = count($content);
                $publicHolilday = new PublicHoliday();
                $publicHolilday->setCountry($formData['country']);
                $publicHolilday->setYear($formData['year']);
                $publicHolilday->setTotalAmount($totalAmountOfPublicHolidays);

                foreach ($content as $singlePublicHoliday) {
                    $month = $singlePublicHoliday['date']['month'];
                    $mappedMonth = $monthMapping[$month];
                    $day = $singlePublicHoliday['date']['day'];

                    $publicHolidaysMonthDay[$mappedMonth][] = $day;
                }

                $publicHolilday->setMonthDay($publicHolidaysMonthDay);

                ///////////////////////////////////////////////////////
                // Count max free days in a row in a year
                ///////////////////////////////////////////////////////
                $maxFreeDaysInARow = $makeApiRequest->getMaxFreeDaysInARowInAYear($this->client, $formData['year'], $countryCode);

                $publicHolilday->setMaxFreeDaysInARow($maxFreeDaysInARow);

                $entityManager->persist($publicHolilday);
                $entityManager->flush();

                $publicHolidayFilterResult = [
                    'public_holidays' => $publicHolidaysMonthDay,
                    'total_amount_of_public_holidays' => $totalAmountOfPublicHolidays,
                    'status' => $typeOfDay,
                    'max_number_of_free_days' => $maxFreeDaysInARow,
                ];

                return $this->render('public_holidays/index.html.twig', [
                    'controller_name' => 'PublicHolidaysController',
                    'public_holiday_form' => $form->createView(),
                    'public_holiday_filter_result' => $publicHolidayFilterResult,
                ]);
            }
        }

        return $this->render('public_holidays/index.html.twig', [
            'controller_name' => 'PublicHolidaysController',
            'public_holiday_form' => $form->createView(),
            'public_holiday_filter_result' => [
                'public_holidays' => '',
                'total_amount_of_public_holidays' => '',
                'status' => '',
                'max_number_of_free_days' => '',
            ],
        ]);
    }
}
