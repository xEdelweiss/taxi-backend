<?php

namespace App\Controller\Api;

use App\Attribute\Output;
use App\Dto\Driver\UpdateDriverPayload;
use App\Dto\Driver\DriverResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Driver')]
#[Route('/api/driver')]
class DriverController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('/me', methods: ['PUT'])]
    #[Output(DriverResponse::class)]
    public function updateMe(#[MapRequestPayload] UpdateDriverPayload $payload): JsonResponse
    {
        $profile = $this->getUser()->getDriverProfile();

        if ($profile === null) {
            throw new \RuntimeException('Driver profile not found.');
        }

        $profile->setOnline($payload->online);

        $this->entityManager->flush();

        return $this->json(new DriverResponse($this->getUser()));
    }

    #[Route('/me', methods: ['GET'])]
    #[Output(DriverResponse::class)]
    public function showMe(): JsonResponse
    {
        return new JsonResponse(new DriverResponse($this->getUser()));
    }
}
