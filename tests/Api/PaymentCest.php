<?php


namespace App\Tests\Api;

use App\Entity\User;
use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;

class PaymentCest
{
    public function stripeCustomerIsCreatedForUser(ApiTester $i): void
    {
        $i->sendPostAsJson('/api/auth/register', [
            'phone' => p(1),
            'password' => '!password!',
        ]);

        $i->seeResponseCodeIs(HttpCode::CREATED);

        $i->seeInRepository(User::class, [
            'id' => 1,
            'phone' => p(1),
            'stripeCustomerId' => 'fake-cid-' . p(1),
        ]);
    }
}
