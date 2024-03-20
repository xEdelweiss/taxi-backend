<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cost')]
class CostController extends AbstractController
{
    #[Route('/estimations', methods: ['POST'])]
    public function estimations(): JsonResponse
    {
        return $this->json([
            'data' => [
                'price' => 100,
            ],
        ]);
    }
}
