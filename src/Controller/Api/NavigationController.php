<?php

namespace App\Controller\Api;

use App\Service\NavigationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/navigation')]
class NavigationController extends AbstractController
{
    public function __construct(
        private readonly NavigationService $navigationService,
    ) {}

    #[Route('/routes', methods: ['POST'])]
    public function calculateRoute(Request $request): Response
    {
        $payload = $request->getPayload()->all();

        $start = [
            $payload['start']['latitude'],
            $payload['start']['longitude'],
        ];
        $finish = [
            $payload['end']['latitude'],
            $payload['end']['longitude'],
        ];

        $route = $this->navigationService->calculateRoute($start, $finish);

        return $this->json([
            'data' => [
                'route' => [
                    'distance' => $route->distance,
                    'duration' => $route->duration,
                ]
            ],
        ]);
    }
}
