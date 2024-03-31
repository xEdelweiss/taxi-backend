<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DebugUiController extends AbstractController
{
    #[Route('/', name: 'app_debug_ui')]
    public function index(): Response
    {
        return $this->render('debug_ui/index.html.twig', [
            'controller_name' => 'DebugUiController',
        ]);
    }
}
