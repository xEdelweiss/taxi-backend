<?php


namespace App\Tests\Acceptance;

use App\Entity\TripOrder;
use App\Entity\User;
use App\Tests\Support\AcceptanceTester;
use Codeception\Util\HttpCode;

class PaymentCest
{
    public function stripeCustomerIsCreatedForUser(AcceptanceTester $i): void
    {
        $i->sendPostAsJson('/api/auth/register', [
            'phone' => p(1),
            'password' => '!password!',
        ]);

        $i->seeResponseCodeIs(HttpCode::CREATED);

        $i->seeInRepository(User::class, [
            'id' => 1,
            'phone' => p(1),
        ]);
        $user = $i->grabEntityFromRepository(User::class, ['id' => 1]);
        $i->assertStringStartsWith('cus_', $user->getStripeCustomerId());
    }

    public function userCanAddPaymentMethod(AcceptanceTester $i): void
    {
        $i->sendRegisterRequest(p(1));
        $i->loginAs(p(1));

        $i->sendPostAsJson('/api/payment/payment-methods');

        $i->seeResponseCodeIs(HttpCode::CREATED);
        $paymentUrl = $i->grabDataFromResponseByJsonPath('$.url')[0];
        $i->assertNotEmpty($paymentUrl);

        $i->sendGet($paymentUrl);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContains('const publicKey = "');
        $i->seeResponseContains('const clientSecret = "');
    }

    public function canHoldPaymentForTrip(AcceptanceTester $i): void
    {
        $i->haveUser(p(1));
        $i->linkCustomerId(p(1));
        $i->haveInRepository(TripOrder::class, [
            'cost' => 1234,
            'currency' => 'USD',
            'status' => 'WAITING_FOR_PAYMENT',
        ]);

        $i->loginAs(p(1));
        $i->sendPostAsJson('/api/payment/holds', [
            'order_id' => 1,
        ]);

        $i->seeResponse(HttpCode::CREATED, [
            'data' => [
                'amount' => 1234,
                'currency' => 'USD',
                'captured' => false,
                'order_id' => 1,
            ],
        ]);

        $i->assertNotEmpty($i->grabDataFromResponseByJsonPath('$.data.id')[0]);
    }

    public function canCaptureHoldedPaymentForTripe(AcceptanceTester $i): void
    {
        $i->haveUser(p(1));
        $i->linkCustomerId(p(1));
        $i->haveInRepository(TripOrder::class, [
            'cost' => 1234,
            'currency' => 'USD',
            'status' => 'WAITING_FOR_PAYMENT',
        ]);
        $i->loginAs(p(1));
        $i->sendPostAsJson('/api/payment/holds', [
            'order_id' => 1,
        ]);
        $i->seeResponseCodeIs(HttpCode::CREATED);
        $holdId = $i->grabDataFromResponseByJsonPath('$.data.id')[0];

        $i->sendPutAsJson("/api/payment/holds/{$holdId}", [
            'captured' => true,
        ]);

        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'amount' => 1234,
                'currency' => 'USD',
                'captured' => true,
                'order_id' => 1,
            ],
        ]);
    }
}
