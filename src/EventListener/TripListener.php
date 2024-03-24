<?php

namespace App\EventListener;

use App\Entity\TripOrder;
use App\Event\Payment\PaymentHeldForOrder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class TripListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[AsEventListener(event: PaymentHeldForOrder::class)]
    public function onPaymentHeldForOrder(PaymentHeldForOrder $event): void
    {
        $order = $this->entityManager->find(TripOrder::class, $event->orderId);

        if (!$order) {
            throw new \InvalidArgumentException("Order [{$event->orderId}] not found");
        }

        if ($order->getStatus() !== 'WAITING_FOR_PAYMENT') {
            // @todo: should move to PaymentService?
            throw new \LogicException('Order should be in WAITING_FOR_PAYMENT status');
        }

        $order->setStatus('WAITING_FOR_DRIVER');

        $this->entityManager->flush();
    }
}
