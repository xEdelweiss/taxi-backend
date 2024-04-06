<?php

namespace App\Controller\Api;

use App\Attribute\Output;
use App\Dto\Geolocation\AddressResponse;
use App\Dto\Geolocation\AddressToCoordinatesPayload;
use App\Dto\Geolocation\CoordinatesResponse;
use App\Dto\Geolocation\CoordinatesToAddressPayload;
use App\Service\Geolocation\GeolocationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Geolocation')]
#[Route('/api/geolocation')]
class GeolocationController extends AbstractController
{
    public function __construct(
        private readonly GeolocationServiceInterface $geolocationService,
    ) {}

    #[Route('/addresses', methods: ['POST'])]
    #[OA\HeaderParameter(name: 'Accept-Language', required: false, schema: new OA\Schema(type: 'string', example: 'uk-UA'))]
    #[Output(AddressResponse::class)]
    public function coordsToAddress(#[MapRequestPayload] CoordinatesToAddressPayload $payload, Request $request): JsonResponse
    {
        $addressDto = $this->geolocationService->getGeocoder()
            ->useLocale($request->getLocale())
            ->coordinatesToAddress(
                $payload->latitude,
                $payload->longitude,
            );

        return $this->json(new AddressResponse($addressDto));
    }

    #[Route('/coordinates', methods: ['POST'])]
    #[OA\HeaderParameter(name: 'Accept-Language', required: false, schema: new OA\Schema(type: 'string', example: 'uk-UA'))]
    #[Output(CoordinatesResponse::class)]
    public function addressToCoords(#[MapRequestPayload] AddressToCoordinatesPayload $payload, Request $request): JsonResponse
    {
        $coordsDto = $this->geolocationService->getGeocoder()
            ->useLocale($request->getLocale())
            ->addressToCoordinates(
                $payload->address,
            );

        return $this->json(new CoordinatesResponse($coordsDto));
    }
}
