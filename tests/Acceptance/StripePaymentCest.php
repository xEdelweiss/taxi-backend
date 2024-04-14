<?php


namespace App\Tests\Acceptance;

use App\Entity\Embeddable\Location;
use App\Entity\Embeddable\Money;
use App\Entity\TripOrder;
use App\Entity\User;
use App\Service\Trip\Enum\TripStatus;
use App\Tests\Support\AcceptanceTester;
use Codeception\Util\HttpCode;

class StripePaymentCest
{
    public function _before(AcceptanceTester $i): void
    {
        $i->skipIfAcceptanceTestsDisabled();
    }

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
        $i->assertStringStartsWith('cus_', $user->getPaymentAccountId());
    }

    public function userCanGetLinkToAddPaymentMethod(AcceptanceTester $i): void
    {
        $i->sendRegisterRequest(p(1));
        $i->loginAs(p(1));

        $i->sendPostAsJson('/api/payment/payment-methods', [
            'return_url' => 'http://localhost/fake-app-redirect',
        ]);

        $i->seeResponseCodeIs(HttpCode::CREATED);
        $paymentUrl = $i->grabDataFromResponseByJsonPath('$.url')[0];
        $i->assertNotEmpty($paymentUrl);

        $i->sendGet($paymentUrl);

        $i->seeResponseCodeIs(HttpCode::OK);
        $response = $i->grabResponse();
        $i->assertRegExp('/const publicKey = ".+";/', $response);
        $i->assertRegExp('/const clientSecret = ".+";/', $response);
    }

    public function canHoldPaymentForTrip(AcceptanceTester $i): void
    {
        $user = $i->haveUser(p(1));
        $i->linkPaymentAccountId(p(1));
        $i->haveInRepository(TripOrder::class, [
            'user' => $user,

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
            'amount' => 1234,
            'currency' => 'USD',
            'captured' => false,
            'order_id' => 1,
        ]);

        $i->assertNotEmpty($i->grabDataFromResponseByJsonPath('$.id')[0]);
    }

    public function canCaptureHeldPaymentForTrip(AcceptanceTester $i): void
    {
        $user = $i->haveUser(p(1));
        $i->linkPaymentAccountId(p(1));
        $i->haveInRepository(TripOrder::class, [
            'user' => $user,

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
