<?php

namespace App\Dto\Payment;

readonly class CreatePaymentMethodResponse
{
    public function __construct(
        public string $url,
    ) {}
}
