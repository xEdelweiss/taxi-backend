<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/rating')]
class RatingController extends AbstractController
{
    #[Route('/records', methods: ['POST'])]
    public function createRecord(): JsonResponse
    {
        return $this->json([
            'data' => [
                'id' => 1,
                'order_id' => 1,
                'rating' => 5,
                'comment' => 'Great driver!',
            ],
        ], Response::HTTP_CREATED);
    }
}
