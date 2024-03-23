<?php

namespace App\Service\Payment;

use App\Service\Payment\Provider\PaymentProviderInterface;

interface PaymentServiceInterface
{
    public function getPaymentProvider(): PaymentProviderInterface;
}
