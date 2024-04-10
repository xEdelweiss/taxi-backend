<?php

namespace App\Controller\Api;

use App\Attribute\Output;
use App\Dto\Trip\CreateOrderPayload;
use App\Dto\Trip\UserOrderResponse;
use App\Dto\Trip\OrderRequestsResponse;
use App\Dto\Trip\ShowOrderResponse;
use App\Dto\Trip\UpdateOrderPayload;
use App\Dto\Trip\UserOrdersResponse;
use App\Entity\Embeddable\Location;
use App\Entity\Embeddable\Money;
use App\Entity\TripOrder;
use App\Service\CostService;
use App\Service\NavigationService;
use App\Service\Trip\Enum\TripStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Trip')]
#[Route('/api/trip')]
class TripController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NavigationService      $navigationService,
        private readonly CostService            $costService,
    ) {}

    #[Route('/orders', methods: ['GET'])]
    #[Output(OrderRequestsResponse::class)]
    public function listOrders(): JsonResponse
    {
        if (!$this->getUser()->isDriver()) {
            $orders = $this->getUser()->getTripOrders();

            return $this->json(new UserOrdersResponse($orders->toArray(), $this->navigationService));
        }

        // @fixme move to order-requests?
        // @todo what about active orders for driver/user?
        $orderRequests = $this->getUser()->getDriverProfile()->getTripOrderRequest();
        $orderRequests = $orderRequests ? [$orderRequests] : [];

        return $this->json(new OrderRequestsResponse($orderRequests, $this->navigationService));
    }

    #[Route('/orders/{order}', methods: ['GET'])]
    #[Output(ShowOrderResponse::class)]
    public function showOrder(TripOrder $order): JsonResponse
    {
        // @todo store route in order
        $route = $this->navigationService->calculateRoute(
            $order->getStart()->toLatLng(),
            $order->getEnd()->toLatLng()
        );

        return $this->json(new UserOrderResponse($order, $route));
    }

    #[Route('/orders', methods: ['POST'])]
    #[Output(UserOrderResponse::class, Response::HTTP_CREATED)]
    public function createOrder(#[MapRequestPayload] CreateOrderPayload $payload): JsonResponse
    {
        // @todo store route in order
        $route = $this->navigationService->calculateRoute(
            $payload->start->toLatLng(),
            $payload->end->toLatLng()
        );
        $cost = new Money($this->costService->calculateCost($route), 'USD');

        $order = new TripOrder($this->getUser());
        $order->setCost($cost);
        $order->setStart(
            new Location($payload->start->address, $payload->start->latitude, $payload->start->longitude)
        );
        $order->setEnd(
            new Location($payload->end->address, $payload->end->latitude, $payload->end->longitude)
        );
        $order->setStatus(TripStatus::WaitingForPayment);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->json(new UserOrderResponse($order, $route), Response::HTTP_CREATED);
    }

    #[Route('/orders/{order}', methods: ['PUT'])]
    #[Output(ShowOrderResponse::class)]
    public function updateOrder(#[MapRequestPayload] UpdateOrderPayload $payload, TripOrder $order): JsonResponse
    {
        $order->setStatus($payload->status);

        $this->entityManager->flush();

        // @todo store route in order
        $route = $this->navigationService->calculateRoute(
            $order->getStart()->toLatLng(),
            $order->getEnd()->toLatLng()
        );

        return $this->json(new UserOrderResponse($order, $route));
    }
}
