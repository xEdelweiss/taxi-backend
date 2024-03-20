<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/geolocation')]
class GeolocationController extends AbstractController
{
    #[Route('/addresses', methods: ['POST'])]
    public function coordsToAddress(): JsonResponse
    {
        return $this->json([
            'data' => [
                'address' => '7th st. Fontanskoyi dorohy',
            ],
        ], Response::HTTP_OK);
    }

    #[Route('/coordinates', methods: ['POST'])]
    public function addressToCoords(): JsonResponse
    {
        return $this->json([
            'data' => [
                'latitude' => 46.451538925795234,
                'longitude' => 30.743980453729417,
            ],
        ], Response::HTTP_OK);
    }
}
