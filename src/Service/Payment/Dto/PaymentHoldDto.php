<?php

namespace App\Service\Payment\Dto;

readonly class PaymentHoldDto
{
    public function __construct(
        public string $id,
    ) {}
}
