<?php

namespace App\Controller\Api;

use App\Dto\RouteDto;
use App\Entity\Embeddable\Location;
use App\Entity\Embeddable\Money;
use App\Entity\TripOrder;
use App\Entity\TripOrderRequest;
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
        private readonly NavigationService      $navigationService,
        private readonly CostService            $costService,
    ) {}

    #[Route('/orders', methods: ['GET'])]
    public function listOrders(): JsonResponse
    {
        if (!$this->getUser()->isDriver()) {
            throw new \RuntimeException('Not implemented for users yet.');
        }

        // @todo what about active orders for driver/user?
        $orderRequests = $this->getUser()->getDriverProfile()->getTripOrderRequest();
        $orderRequests = $orderRequests ? [$orderRequests] : [];

        return $this->json([
            'data' => array_map(fn(TripOrderRequest $orderRequest) => [
                'id' => $orderRequest->getTripOrder()->getId(),
                'status' => $orderRequest->getTripOrder()->getStatus(),
                'start' => $this->locationToArray($orderRequest->getTripOrder()->getStart()),
                'end' => $this->locationToArray($orderRequest->getTripOrder()->getEnd()),

                // @fixme is it correct to calculate route time here?
                'trip_time' => $this->calculateRoute($orderRequest->getTripOrder())->duration,
            ], $orderRequests),
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
        $cost = new Money($this->costService->calculateCost($route), 'USD');

        $order = new TripOrder();
        $order->setCost($cost);
        $order->setStart(Location::fromArray($payload['start']));
        $order->setEnd(Location::fromArray($payload['end']));
        $order->setStatus(TripStatus::WaitingForPayment);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->json([
            'data' => [
                'id' => $order->getId(),
                'status' => $order->getStatus(),
                'start' => $this->locationToArray($order->getStart()),
                'end' => $this->locationToArray($order->getEnd()),
                'price' => [
                    'amount' => $order->getCost()->getAmount(),
                    'currency' => $order->getCost()->getCurrency(),
                ],
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

    private function locationToArray(Location $location): array
    {
        return [
            'latitude' => $location->getLatitude(),
            'longitude' => $location->getLongitude(),
            'address' => $location->getAddress(),
        ];
    }

    private function calculateRoute(TripOrder $order): RouteDto
    {
        return $this->navigationService->calculateRoute(
            [$order->getStart()->getLatitude(), $order->getStart()->getLongitude()],
            [$order->getEnd()->getLatitude(), $order->getEnd()->getLongitude()]
        );
    }
}
