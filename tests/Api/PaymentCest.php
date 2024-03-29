<?php


namespace App\Tests\Api;

use App\Entity\TripOrder;
use App\Entity\User;
use App\Service\Trip\Enum\TripStatus;
use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;

class PaymentCest
{
    public function paymentAccountIsCreatedForUser(ApiTester $i): void
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
        $i->assertNotEmpty($user->getPaymentAccountId());
    }

    public function userCanGetLinkToAddPaymentMethod(ApiTester $i): void
    {
        $i->haveUser(p(1));
        $i->linkPaymentAccountId(p(1));
        $i->loginAs(p(1));

        $i->sendPostAsJson('/api/payment/payment-methods', [
            'return_url' => 'http://localhost/fake-app-redirect',
        ]);

        $i->seeResponseCodeIs(HttpCode::CREATED);
        $i->assertNotEmpty($i->grabDataFromResponseByJsonPath('$.url')[0]);
    }

    public function addPaymentMethodUrlShowsForm(ApiTester $i): void
    {
        $i->makeAddPaymentMethodsRequestAsUser(p(1));

        $i->sendGet($i->grabDataFromResponseByJsonPath('$.url')[0]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContains('fake-add-payment-method-form');
    }

    public function userShouldBeRedirectedToAppAfterPaymentMethodAdded(ApiTester $i): void
    {
        $i->makeAddPaymentMethodsRequestAsUser(p(1));
        $addPaymentMethodUrl = $i->grabDataFromResponseByJsonPath('$.url')[0];

        $i->amOnPage($addPaymentMethodUrl);
        $i->see('Add payment method');

        $i->stopFollowingRedirects();
        $i->click('Add payment method');

        $i->seeInCurrentUrl('/payment-method/success');

        $i->seeResponseCodeIsRedirection();
        $i->followRedirect();

        $i->seeInCurrentUrl('/fake-app-redirect');
        $i->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function canHoldPaymentForTrip(ApiTester $i): void
    {
        $i->haveUser(p(1));
        $i->linkPaymentAccountId(p(1));
        $i->haveInRepository(TripOrder::class, [
            'cost' => 1234,
            'currency' => 'USD',
            'status' => TripStatus::WaitingForPayment,
        ]);

        $i->loginAs(p(1));
        $i->sendPostAsJson('/api/payment/holds', [
            'order_id' => 1,
        ]);

        $i->seeResponse(HttpCode::CREATED, [
            'data' => [
                'id' => 'fake-uid-1-oid-1',
                'amount' => 1234,
                'currency' => 'USD',
                'captured' => false,
                'order_id' => 1,
            ],
        ]);
    }

    public function canCaptureHeldPaymentForTripe(ApiTester $i): void
    {
        $i->haveUser(p(1));
        $i->linkPaymentAccountId(p(1));
        $i->haveInRepository(TripOrder::class, [
            'cost' => 1234,
            'currency' => 'USD',
            'status' => TripStatus::WaitingForPayment,
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
