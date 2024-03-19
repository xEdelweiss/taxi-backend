<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/api/auth/me', methods: ['GET'])]
    public function me(): Response
    {
        if (!$this->getUser()) {
            return $this->json(['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $this->getUser()->getId(),
            'phone' => $this->getUser()->getPhone(),
        ]);
    }
}
