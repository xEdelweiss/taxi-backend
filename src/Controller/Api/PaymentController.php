<?php

namespace App\Controller\Api;

use App\Entity\TripOrder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('/payments', methods: ['POST'])]
    public function preparePayment(): JsonResponse
    {
        return $this->json([
            'data' => [
                'id' => 1,
                'order_id' => 1,
                'amount' => 100,
                'url' => '/api/payment/fake-provider?order_id=1',
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/fake-provider', methods: ['GET'])]
    public function fakeProvider(#[Autowire('%kernel.environment%')] string $env, Request $request): Response
    {
        if ($env === 'production') {
            $this->createNotFoundException();
        }

        $orderId = $request->query->get('order_id');
        $order = $this->entityManager->getRepository(TripOrder::class)->find($orderId);
        $order->setStatus('WAITING_FOR_DRIVER');

        $this->entityManager->flush();

        return new Response(status: Response::HTTP_OK);
    }
}
