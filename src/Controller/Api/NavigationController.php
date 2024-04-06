<?php

namespace App\Controller\Api;

use App\Attribute\Output;
use App\Dto\Navigation\CreateRoutePayload;
use App\Dto\Navigation\RouteResponse;
use App\Service\NavigationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Navigation')]
#[Route('/api/navigation')]
class NavigationController extends AbstractController
{
    public function __construct(
        private readonly NavigationService $navigationService,
    ) {}

    #[Route('/routes', methods: ['POST'])]
    #[Output(RouteResponse::class)]
    public function calculateRoute(#[MapRequestPayload] CreateRoutePayload $payload): Response
    {
        $route = $this->navigationService->calculateRoute(
            $payload->start->toLatLng(),
            $payload->end->toLatLng(),
        );

        return $this->json(new RouteResponse($route));
    }
}
