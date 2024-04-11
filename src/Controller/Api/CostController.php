<?php

namespace App\Controller\Api;

use App\Attribute\Output;
use App\Dto\Cost\CreateEstimationPayload;
use App\Dto\Cost\CreateEstimationResponse;
use App\Service\CostService;
use App\Service\NavigationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Cost Estimation')]
#[Route('/api/cost')]
class CostController extends AbstractController
{
    public function __construct(
        private NavigationService $navigationService,
        private CostService       $costService,
    ) {}

    #[Route('/estimations', methods: ['POST'])]
    #[Output(CreateEstimationResponse::class, Response::HTTP_CREATED)]
    public function estimations(#[MapRequestPayload] CreateEstimationPayload $payload): JsonResponse
    {
        $route = $this->navigationService->calculateRoute(
            $payload->start,
            $payload->end,
        );

        return $this->json(new CreateEstimationResponse(
            $this->costService->calculateCost($route),
            'USD',
        ), Response::HTTP_CREATED);
    }
}
