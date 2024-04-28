<?php

namespace App\Service;

use App\Dto\Payment\PaymentHoldDto;
use App\Entity\TripOrder;
use App\Entity\User;
use App\Event\Payment\PaymentHeldForOrder;
use App\Service\Payment\PaymentServiceInterface;
use App\Service\Payment\Provider\PaymentProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class PaymentService implements PaymentServiceInterface, PaymentProviderInterface
{
    public function __construct(
        private PaymentProviderInterface $paymentProvider,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function createCustomer(User $user): string
    {
        return $this->paymentProvider->createCustomer($user);
    }

    public function getAddPaymentLink(User $user, string $returnUrl): string
    {
        return $this->paymentProvider->getAddPaymentLink($user, $returnUrl);
    }

    public function holdPaymentForOrder(User $user, TripOrder $order): PaymentHoldDto
    {
        $result = $this->paymentProvider->holdPaymentForOrder($user, $order);
        $this->eventDispatcher->dispatch(new PaymentHeldForOrder($user->getId(), $order->getId(), $result->id));

        return $result;
    }

    public function capturePaymentHold(PaymentHoldDto $hold): void
    {
        $this->paymentProvider->capturePaymentHold($hold);
    }

    public function getOrderByPaymentHold(PaymentHoldDto $hold): int
    {
        return $this->paymentProvider->getOrderByPaymentHold($hold);
    }
}
