<?php

namespace App\Controller\Api;

use App\Attribute\Output;
use App\Dto\Healthcheck\HealthcheckResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Healthcheck')]
class HealthcheckController extends AbstractController
{
    #[Route('/api/healthcheck', methods: ['GET'])]
    #[Output(HealthcheckResponse::class)]
    public function index(): Response
    {
        return $this->json(new HealthcheckResponse('Working.'));
    }
}
