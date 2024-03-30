<?php

namespace App\EventListener;

use App\Entity\TripOrder;
use App\Entity\TripOrderRequest;
use App\Event\Payment\PaymentHeldForOrder;
use App\Event\TripOrderPublished;
use App\Exception\TodoException;
use App\Service\MatchingService;
use App\Service\Trip\Enum\TripStatus;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class TripListener
{
    public function __construct(
        private readonly EntityManagerInterface   $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MatchingService          $matchingService,
    ) {}

    #[AsEventListener(event: PaymentHeldForOrder::class)]
    public function onPaymentHeldForOrder(PaymentHeldForOrder $event): void
    {
        $order = $this->findOrderOrFail($event->orderId);

        // @todo should move to PaymentService?
        $this->ensureStatus($order, TripStatus::WaitingForPayment);

        $order->setPaymentHoldId($event->paymentHoldId);
        $order->setStatus(TripStatus::WaitingForDriver);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new TripOrderPublished($order->getId()));
    }

    #[AsEventListener(event: TripOrderPublished::class)]
    public function onTripOrderPublished(TripOrderPublished $event): void
    {
        $order = $this->findOrderOrFail($event->orderId);

        // @todo is it ok to check status here?
        $this->ensureStatus($order, TripStatus::WaitingForDriver); // @todo should be LookingForDriver?

        // @todo filter only for drivers that are not busy
        $closestDriverProfile = $this->matchingService->findMatchingDriver($order->getStart());

        if (!$closestDriverProfile) {
            throw new TodoException('No drivers found. Implement retry logic');
        }

        $orderRequest = new TripOrderRequest($closestDriverProfile, $order);

        $this->entityManager->persist($orderRequest);
        $this->entityManager->flush();
    }

    private function findOrderOrFail(int $orderId): TripOrder
    {
        $order = $this->entityManager->find(TripOrder::class, $orderId);

        if (!$order) {
            throw new \InvalidArgumentException("Order [{$orderId}] not found");
        }

        return $order;
    }

    private function ensureStatus(TripOrder $order, TripStatus $expectedStatus): void
    {
        if ($order->getStatus() !== $expectedStatus) {
            throw new \LogicException(sprintf("Order should be in %s status", $expectedStatus->value));
        }
    }
}
