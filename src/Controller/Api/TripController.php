<?php

namespace App\Controller\Api;

use App\Dto\MoneyDto;
use App\Entity\TripOrder;
use App\Service\CostService;
use App\Service\NavigationService;
use App\Service\Trip\Enum\TripStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/trip')]
class TripController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NavigationService $navigationService,
        private readonly CostService $costService,
    ) {}

    #[Route('/orders', methods: ['GET'])]
    public function listOrders(): JsonResponse
    {
        $orders = $this->entityManager->getRepository(TripOrder::class)->findAll();

        return $this->json([
            'data' => [
                array_map(static fn (TripOrder $order) => [
                    'id' => $order->getId(),
                    'status' => $order->getStatus(),
                    'start' => [
                        'latitude' => 46.4273814334286,
                        'longitude' => 30.751279752912698,
                        'address' => '7th st. Fontanskoyi dorohy',
                    ],
                    'end' => [
                        'latitude' => 46.451538925795234,
                        'longitude' => 30.743980453729417,
                        'address' => 'Sehedska Street, 5',
                    ],
                ], $orders),
            ],
        ]);
    }

    #[Route('/orders/{order}', methods: ['GET'])]
    public function showOrder(TripOrder $order): JsonResponse
    {
        return $this->json([
            'data' => [
                'id' => $order->getId(),
                'status' => $order->getStatus(),
            ],
        ]);
    }

    #[Route('/orders', methods: ['POST'])]
    public function createOrder(Request $request): JsonResponse
    {
        $payload = $request->getPayload()->all();
        $start = [
            $payload['start']['latitude'],
            $payload['start']['longitude'],
        ];
        $end = [
            $payload['end']['latitude'],
            $payload['end']['longitude'],
        ];

        $route = $this->navigationService->calculateRoute($start, $end);
        $cost = new MoneyDto($this->costService->calculateCost($route), 'USD');

        $order = new TripOrder();
        $order->setCost($cost);
        $order->setStatus(TripStatus::WaitingForPayment);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->json([
            'data' => [
                'id' => $order->getId(),
                'status' => $order->getStatus(),
                'start' => $payload['start'], // @fixme update from route
                'end' => $payload['end'], // @fixme update from route
                'price' => [
                    'amount' => $order->getCost()->amount,
                    'currency' => $order->getCost()->currency,
                ],
                'driver_arrival_time' => 10,
                'trip_time' => $route->duration,
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/orders/{order}', methods: ['PUT'])]
    public function updateOrder(Request $request, TripOrder $order): JsonResponse
    {
        $newStatus = TripStatus::from($request->getPayload()->get('status'));
        $order->setStatus($newStatus);

        $this->entityManager->flush();

        return $this->json([
            'data' => [
                'id' => $order->getId(),
                'status' => $order->getStatus(),
            ],
        ]);
    }
}
