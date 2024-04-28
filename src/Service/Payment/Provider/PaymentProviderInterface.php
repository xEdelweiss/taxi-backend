<?php

namespace App\Service\Payment\Provider;

use App\Dto\Payment\PaymentHoldDto;
use App\Entity\TripOrder;
use App\Entity\User;

interface PaymentProviderInterface
{
    public function createCustomer(User $user): string;

    public function getAddPaymentLink(User $user, string $returnUrl): string;

    public function holdPaymentForOrder(User $user, TripOrder $order): PaymentHoldDto;

    public function capturePaymentHold(PaymentHoldDto $hold): void;

    public function getOrderByPaymentHold(PaymentHoldDto $hold): int;
}
