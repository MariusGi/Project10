<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\PublicHoliday;
use App\Form\PublicHolidayType;
use App\Repository\CountryRepository;
use App\Repository\PublicHolidayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function index(Request $request, CountryRepository $countryRepository,
                          PublicHolidayRepository $publicHolidayRepository): Response
    {
        $publicHolidayFilterResult = [];
        $entityManager = $this->getDoctrine()->getManager();
        $isCountryTableHasData = $countryRepository->findBy(array(), null, 1);

        ///////////////////////////////////////////////////////
        // Add countries in API to Country table if it is empty
        ///////////////////////////////////////////////////////
        if (!$isCountryTableHasData) {
            $content = [];
            $response = $this->client->request(
                'GET',
                'https://kayaposoft.com/enrico/json/v2.0/?action=getSupportedCountries'
            );

            try {
                $content = $response->toArray();
            } catch (ClientExceptionInterface $e) {
            } catch (DecodingExceptionInterface $e) {
            } catch (RedirectionExceptionInterface $e) {
            } catch (ServerExceptionInterface $e) {
            } catch (TransportExceptionInterface $e) {
            }

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

            $content = [];
            $formData = $form->getData();

            ///////////////////////////////////////////////////////
            // Check if data already exists in db
            ///////////////////////////////////////////////////////
            if ($publicHolidayData = $publicHolidayRepository->findOneBy([
                'country' => $formData['country']->getId(),
                'year' => $formData['year'],
            ])) {

                // TODO make request to db
                die('make a request to db');

            }

            $countryData = $countryRepository->findOneBy(['id' => $formData['country']->getId()]);
            $countryCode = $countryData->getCountryCode();
            $dataAvailableFromYear = $countryData->getHolidaysAvailableFromYear();
            $dataAvailableToYear = $countryData->getHolidaysAvailableToYear();

            ///////////////////////////////////////////////////////
            // Have necessary data to make api request
            ///////////////////////////////////////////////////////
            if ($formData['year'] >= $dataAvailableFromYear && $formData['year'] <= $dataAvailableToYear) {
                $response = $this->client->request(
                    'GET',
                    "https://kayaposoft.com/enrico/json/v2.0/?action=getHolidaysForYear&year={$formData['year']}
                     &country={$countryCode}&holidayType=public_holiday"
                );

                try {
                    $content = $response->toArray();
                } catch (ClientExceptionInterface $e) {
                } catch (DecodingExceptionInterface $e) {
                } catch (RedirectionExceptionInterface $e) {
                } catch (ServerExceptionInterface $e) {
                } catch (TransportExceptionInterface $e) {
                }

                ///////////////////////////////////////////////////////
                // Insert data into public_holidays table
                ///////////////////////////////////////////////////////
                $publicHolidaysMonthDay = [];
                $totalAmountOfPublicHolidays = count($content);
                $publicHolilday = new PublicHoliday();
                $publicHolilday->setCountry($formData['country']);
                $publicHolilday->setYear($formData['year']);
                $publicHolilday->setTotalAmount($totalAmountOfPublicHolidays);

                foreach ($content as $singlePublicHoliday) {
                    $month = $singlePublicHoliday['date']['month'];
                    $day = $singlePublicHoliday['date']['day'];

                    $publicHolidaysMonthDay[$month][] = $day;
                }

                $publicHolilday->setMonthDay($publicHolidaysMonthDay);

                ///////////////////////////////////////////////////////
                // Count max free days in a row in a year
                ///////////////////////////////////////////////////////
                $maxFreeDaysInARow = 0;
                $currentFreeDaysInARow = 0;

                for ($monthNumber = 1; $monthNumber < 13; $monthNumber++) {
                    for ($dayNumber = 1; $dayNumber < 32; $dayNumber++) {
                        $content = [];
                        $response = $this->client->request(
                            'GET',
                            "https://kayaposoft.com/enrico/json/v2.0/?action=isWorkDay&date={$dayNumber}-{$monthNumber}-{$formData['year']}&country={$countryCode}"
                        );

                        try {
                            $content = $response->toArray();
                        } catch (ClientExceptionInterface $e) {
                        } catch (DecodingExceptionInterface $e) {
                        } catch (RedirectionExceptionInterface $e) {
                        } catch (ServerExceptionInterface $e) {
                        } catch (TransportExceptionInterface $e) {
                        }

                        // Check if dayNumber is valid
                        if (isset($content['error'])) {
                            break;
                        }

                        if ($content['isWorkDay'] == true) {

                            if ($maxFreeDaysInARow < $currentFreeDaysInARow) {
                                $maxFreeDaysInARow = $currentFreeDaysInARow;
                            }

                            $currentFreeDaysInARow = 0;
                        } else {
                            $currentFreeDaysInARow++;
                        }

                    }
                }

                $publicHolilday->setMaxFreeDaysInARow($maxFreeDaysInARow);

                $entityManager->persist($publicHolilday);
                $entityManager->flush();

                $publicHolidayFilterResult = [
                    'public_holidays' => '',
                    'total_amount_of_public_holidays' => $totalAmountOfPublicHolidays,
                    'status' => '',
                    'max_number_of_free_days' => $maxFreeDaysInARow,
                ];
            }
        }

        return $this->render('public_holidays/index.html.twig', [
            'controller_name' => 'PublicHolidaysController',
            'public_holiday_form' => $form->createView(),
            'public_holiday_filter_result' => $publicHolidayFilterResult,
        ]);
    }
}
