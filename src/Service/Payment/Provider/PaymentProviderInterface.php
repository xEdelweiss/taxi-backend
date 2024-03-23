<?php

namespace App\Service\Payment\Provider;

use App\Entity\User;

interface PaymentProviderInterface
{
    public function createCustomer(User $user): string;
}
