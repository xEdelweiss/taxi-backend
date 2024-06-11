<?php

namespace App\Dto\Payment;

readonly class PaymentHoldDto
{
    public function __construct(
        public string $id,
    ) {}
}
