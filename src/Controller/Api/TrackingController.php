<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tracking')]
class TrackingController extends AbstractController
{
    #[Route('/locations', methods: ['POST'])]
    public function trackLocation(): Response
    {
        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
