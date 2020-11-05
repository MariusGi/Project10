<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicHolidaysController extends AbstractController
{
    /**
     * @Route("/public-holidays", name="public_holidays")
     */
    public function index(): Response
    {
        return $this->render('public_holidays/index.html.twig', [
            'controller_name' => 'PublicHolidaysController',
        ]);
    }
}
