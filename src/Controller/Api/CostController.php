<?php

namespace App\Controller\Api;

use App\Service\CostService;
use App\Service\NavigationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cost')]
class CostController extends AbstractController
{
    public function __construct(
        private NavigationService $navigationService,
        private CostService $costService,
    ) {}

    #[Route('/estimations', methods: ['POST'])]
    public function estimations(Request $request): JsonResponse
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
                'cost' => $this->costService->calculateCost($route),
                'currency' => 'USD',
            ],
        ], Response::HTTP_CREATED);
    }
}
