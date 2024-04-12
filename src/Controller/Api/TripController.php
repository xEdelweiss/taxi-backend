<?php

namespace App\Controller\Api;

use App\Attribute\Output;
use App\Document\TrackingLocation;
use App\Dto\LocationDto;
use App\Dto\RouteDto;
use App\Dto\Trip\CreateOrderPayload;
use App\Dto\Trip\ListOrdersQuery;
use App\Dto\Trip\TripOrderResponse;
use App\Dto\Trip\TripOrdersResponse;
use App\Dto\Trip\UpdateOrderPayload;
use App\Entity\TripOrder;
use App\Service\NavigationService;
use App\Service\Trip\Enum\TripStatus;
use App\Service\TripService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Trip')]
#[Route('/api/trip')]
class TripController extends AbstractController
{
    public function __construct(
        private readonly TripService            $tripService,
        private readonly EntityManagerInterface $entityManager,
        private readonly NavigationService      $navigationService,
        private readonly DocumentManager        $documentManager,
    ) {}

    #[Route('/orders', methods: ['GET'])]
    #[Output(TripOrdersResponse::class)]
    public function listOrders(#[MapQueryString] ?ListOrdersQuery $query): JsonResponse
    {
        $orders = $this->entityManager->getRepository(TripOrder::class)
            ->findUserOrdersIncludingRequests(
                $this->getUser(),
                $query?->status?->toTripStatusList(),
            );

        return $this->json(new TripOrdersResponse($orders));
    }

    #[Route('/orders/{order}', methods: ['GET'])]
    #[Output(TripOrderResponse::class)]
    public function showOrder(TripOrder $order): JsonResponse
    {
        $route = $this->calculateRoute($order);

        if ($order->getStatus()->isActive()) {
            $startEta = $this->tripService->calculateEta(LocationDto::fromEmbeddable($order->getStart()));

            $driver = $order->getTripOrderRequest()?->getDriver()?->getUser();

            $driverLocation = $driver
                ? $this->documentManager->getRepository(TrackingLocation::class)->findByUser($driver)
                : null;
        } else {
            $startEta = null;
            $driverLocation = null;
        }

        return $this->json(new TripOrderResponse($order, $route, $startEta, $driverLocation));
    }

    #[Route('/orders', methods: ['POST'])]
    #[Output(TripOrderResponse::class, Response::HTTP_CREATED)]
    public function createOrder(#[MapRequestPayload] CreateOrderPayload $payload): JsonResponse
    {
        $route = $this->navigationService->calculateRoute(
            $payload->start,
            $payload->finish,
        );

        $order = $this->tripService->createOrder(
            $this->getUser(),
            $route,
        );

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $startEta = $this->tripService->calculateEta(LocationDto::fromEmbeddable($order->getStart()));

        return $this->json(new TripOrderResponse($order, $route, $startEta), Response::HTTP_CREATED);
    }

    #[Route('/orders/{order}', methods: ['PUT'])]
    #[Output(TripOrderResponse::class)]
    public function updateOrder(#[MapRequestPayload] UpdateOrderPayload $payload, TripOrder $order): JsonResponse
    {
        if ($payload->status === TripStatus::CanceledByUser || $payload->status === TripStatus::CanceledByDriver) {
            // @todo refund
            // $this->paymentService->refund($order);
            $order->setPaymentHoldId(null);
        }

        $order->setStatus($payload->status);

        if (!$order->getStatus()->isActive() && $order->getTripOrderRequest()) {
            $this->entityManager->remove($order->getTripOrderRequest());
        }

        $this->entityManager->flush();

        return $this->showOrder($order);
    }

    private function calculateRoute(TripOrder $order): RouteDto
    {
        return $this->navigationService->calculateRoute(
            LocationDto::fromEmbeddable($order->getStart()),
            LocationDto::fromEmbeddable($order->getEnd()),
        );
    }
}
