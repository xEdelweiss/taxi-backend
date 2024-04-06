<?php

namespace App\Dto\Payment;

readonly class CapturePaymentPayload
{
    public function __construct(
        public bool $captured,
    ) {}
}
