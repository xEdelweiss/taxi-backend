<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HealthcheckController extends AbstractController
{
    #[Route('/api/healthcheck', methods: ['GET'])]
    public function index(): Response
    {
        return $this->json(['message' => 'Working.']);
    }
}
