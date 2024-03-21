<?php

namespace App\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/driver')]
class DriverController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('/me', methods: ['PUT'])]
    public function updateMe(Request $request): JsonResponse
    {
        $profile = $this->getUser()->getDriverProfile();

        if ($profile === null) {
            throw new \RuntimeException('Driver profile not found.');
        }

        $profile->setOnline($request->getPayload()->get('online'));

        $this->entityManager->flush();

        return new JsonResponse([
            'data' => [
                'id' => $this->getUser()->getId(),
                'online' => $profile->isOnline(),
            ],
        ]);
    }

    #[Route('/me', methods: ['GET'])]
    public function showMe(): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse([
            'data' => [
                'id' => $user->getId(),
                'online' => $user->getDriverProfile()->isOnline(),
            ],
        ]);
    }
}
