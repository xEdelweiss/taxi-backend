<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/driver')]
class DriverController extends AbstractController
{
    #[Route('/me', methods: ['PUT'])]
    public function updateMe(): JsonResponse
    {
        return new JsonResponse([
            'data' => [
                'id' => $this->getUser()->getId(),
                'status' => 'AVAILABLE',
            ],
        ]);
    }

    #[Route('/me', methods: ['GET'])]
    public function showMe(): JsonResponse
    {
        return new JsonResponse([
            'data' => [
                'id' => $this->getUser()->getId(),
                'status' => 'AVAILABLE',
            ],
        ]);
    }
}
