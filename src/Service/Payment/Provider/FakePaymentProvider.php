<?php

namespace App\Service\Payment\Provider;

use App\Entity\User;

class FakePaymentProvider implements PaymentProviderInterface
{
    public function createCustomer(User $user): string
    {
        return 'fake-cid-' . $user->getPhone();
    }
}
