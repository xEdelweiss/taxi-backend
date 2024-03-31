<?php

namespace App\Controller\Api;

use App\Exception\Geolocation\AddressNotFound;
use App\Service\Geolocation\GeolocationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/geolocation')]
class GeolocationController extends AbstractController
{
    public function __construct(
        private readonly GeolocationServiceInterface $geolocationService,
    ) {}

    #[Route('/addresses', methods: ['POST'])]
    public function coordsToAddress(Request $request): JsonResponse
    {
        $latitude = $request->getPayload()->get('latitude');
        $longitude = $request->getPayload()->get('longitude');

        $addressDto = $this->geolocationService->getGeocoder()
            ->useLocale($request->getLocale())
            ->coordinatesToAddress($latitude, $longitude);

        return $this->json([
            'data' => [
                'address' => $addressDto->address,
            ],
        ], Response::HTTP_OK);
    }

    #[Route('/coordinates', methods: ['POST'])]
    public function addressToCoords(Request $request): JsonResponse
    {
        $address = $request->getPayload()->get('address');

        try {
            $coordsDto = $this->geolocationService->getGeocoder()
                ->useLocale($request->getLocale())
                ->addressToCoordinates($address);
        } catch (AddressNotFound $e) {
            return $this->json([
                'data' => null,
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'data' => [
                'latitude' => $coordsDto->latitude,
                'longitude' => $coordsDto->longitude,
            ],
        ], Response::HTTP_OK);
    }
}
