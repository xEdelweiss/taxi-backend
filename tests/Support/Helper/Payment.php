<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Entity\User;
use App\Tests\Support\TestData;

class Payment extends \Codeception\Module
{
    public function linkPaymentAccountId(User|string $user, string $paymentAccountId = TestData::STRIPE_CUSTOMER_ID): User
    {
        $user = $this->ensureUserEntity($user);
        $user->setPaymentAccountId($paymentAccountId);
        $this->getModule('Doctrine2')->flushToDatabase();

        return $user;
    }

    private function ensureUserEntity(User|string $user): User
    {
        return $user instanceof User
            ? $user
            : $this->getModule('Doctrine2')->grabEntityFromRepository(User::class, ['phone' => $user]);
    }
}
