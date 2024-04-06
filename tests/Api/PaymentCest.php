<?php


namespace App\Tests\Api;

use App\Entity\Embeddable\Location;
use App\Entity\Embeddable\Money;
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
            'cost' => new Money(1234, 'USD'),
            'status' => TripStatus::WaitingForPayment,

            // @fixme not required
            'start' => new Location('7th st. Fontanskoyi dorohy', 46.4273814334286, 30.751279752912698),
            'end' => new Location('Sehedska Street, 5', 46.423173199108106, 30.74705368639186)
        ]);

        // @fixme not required: better to mute the event/listener that looks for a driver
        $i->haveDriver(p(2));
        $i->moveToLocation(p(2), 46.42738, 30.75128);

        $i->loginAs(p(1));
        $i->sendPostAsJson('/api/payment/holds', [
            'order_id' => 1,
        ]);

        $i->seeResponse(HttpCode::CREATED, [
            'id' => 'fake-uid-1-oid-1',
            'amount' => 1234,
            'currency' => 'USD',
            'captured' => false,
            'order_id' => 1,
        ]);
    }

    public function canCaptureHeldPaymentForTrip(ApiTester $i): void
    {
        $i->haveUser(p(1));
        $i->linkPaymentAccountId(p(1));
        $i->haveInRepository(TripOrder::class, [
            'cost' => new Money(1234, 'USD'),
            'status' => TripStatus::WaitingForPayment,

            // @fixme not required
            'start' => new Location('7th st. Fontanskoyi dorohy', 46.4273814334286, 30.751279752912698),
            'end' => new Location('Sehedska Street, 5', 46.423173199108106, 30.74705368639186)
        ]);

        // @fixme not required: better to mute the event/listener that looks for a driver
        $i->haveDriver(p(2));
        $i->moveToLocation(p(2), 46.42738, 30.75128);

        $i->loginAs(p(1));
        $i->sendPostAsJson('/api/payment/holds', [
            'order_id' => 1,
        ]);
        $i->seeResponseCodeIs(HttpCode::CREATED);
        $holdId = $i->grabDataFromResponseByJsonPath('$.id')[0];

        $i->sendPutAsJson("/api/payment/holds/{$holdId}", [
            'captured' => true,
        ]);

        $i->seeResponse(HttpCode::OK, [
            'amount' => 1234,
            'currency' => 'USD',
            'captured' => true,
            'order_id' => 1,
        ]);
    }
}
