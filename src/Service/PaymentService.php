<?php

namespace App\Service;

use App\Service\Payment\PaymentServiceInterface;
use App\Service\Payment\Provider\PaymentProviderInterface;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private readonly PaymentProviderInterface $paymentProvider,
    ) {}

    public function getPaymentProvider(): PaymentProviderInterface
    {
        return $this->paymentProvider;
    }
}
