<?php

namespace App\Api;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class MakeApiRequest
{
    public function getSupportedCountries($client)
    {
        $content = [];
        $response = $client->request(
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

        return $content;
    }

    public function getTypeOfDay($client, $countryCode)
    {
        $typeOfDay = 'Free day';
        $currentDate = date('d-m-Y');
        $response = $client->request(
            'GET',
            "https://kayaposoft.com/enrico/json/v2.0?action=isWorkDay&date={$currentDate}&country={$countryCode}"
        );

        try {
            $content = $response->toArray();
        } catch (ClientExceptionInterface $e) {
        } catch (DecodingExceptionInterface $e) {
        } catch (RedirectionExceptionInterface $e) {
        } catch (ServerExceptionInterface $e) {
        } catch (TransportExceptionInterface $e) {
        }

        if (isset($content['isWorkDay']) && $content['isWorkDay'] === true) {
            $typeOfDay = 'Workday';
        }

        if (isset($content['isWorkDay']) && $content['isWorkDay'] === false) {
            $response = $client->request(
                'GET',
                "https://kayaposoft.com/enrico/json/v2.0?action=isPublicHoliday&date={$currentDate}&country={$countryCode}"
            );

            try {
                $content = $response->toArray();
            } catch (ClientExceptionInterface $e) {
            } catch (DecodingExceptionInterface $e) {
            } catch (RedirectionExceptionInterface $e) {
            } catch (ServerExceptionInterface $e) {
            } catch (TransportExceptionInterface $e) {
            }

            if ($content['isPublicHoliday'] === true) {
                $typeOfDay = 'Public holiday';
            }
        }

        return $typeOfDay;
    }

    public function getPublicHolidaysForYear($client, $year, $countryCode)
    {
        $content = [];
        $response = $client->request(
            'GET',
            "https://kayaposoft.com/enrico/json/v2.0/?action=getHolidaysForYear&year={$year}
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

        return $content;
    }

    public function getMaxFreeDaysInARowInAYear($client, $year, $countryCode)
    {
        $maxFreeDaysInARow = 0;
        $currentFreeDaysInARow = 0;

        for ($monthNumber = 1; $monthNumber < 13; $monthNumber++) {
            for ($dayNumber = 1; $dayNumber < 32; $dayNumber++) {
                $content = [];
                $response = $client->request(
                    'GET',
                    "https://kayaposoft.com/enrico/json/v2.0/?action=isWorkDay&date={$dayNumber}-{$monthNumber}-{$year}&country={$countryCode}"
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

        return $maxFreeDaysInARow;
    }
}