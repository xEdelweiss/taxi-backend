<?php

namespace App\Event\Payment;

readonly class PaymentHeldForOrder
{
    public function __construct(
        public int $userId,
        public int $orderId,
        public string $paymentHoldId,
    ) {}
}
