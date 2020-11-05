<?php

namespace App\Controller;

use App\Entity\Country;
use App\Repository\CountryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @param CountryRepository $countryRepository
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function index(CountryRepository $countryRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $isCountryTableHasData = $countryRepository->findBy(array(), null, 1);

        if (!$isCountryTableHasData) {
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

                $entityManager->persist($country);
            }

            $entityManager->flush();
        }

        return $this->render('public_holidays/index.html.twig', [
            'controller_name' => 'PublicHolidaysController',
        ]);
    }
}
