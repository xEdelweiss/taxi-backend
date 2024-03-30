<?php


namespace App\Tests\Api;

use App\Entity\Embeddable\Location;
use App\Entity\Embeddable\Money;
use App\Entity\TripOrder;
use App\Event\Payment\PaymentHeldForOrder;
use App\Service\Trip\Enum\TripStatus;
use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;
use Psr\EventDispatcher\EventDispatcherInterface;

class TripCest
{
    public function userCanCreateOrder(ApiTester $i): void
    {
        $i->amLoggedInAsNewUser(p(1));

        $i->sendPostAsJson('/api/trip/orders', [
            'start' => [
                'latitude' => 46.4273814334286,
                'longitude' => 30.751279752912698,
                'address' => '7th st. Fontanskoyi dorohy',
            ],
            'end' => [
                'latitude' => 46.423173199108106,
                'longitude' => 30.74705368639186,
                'address' => 'Sehedska Street, 5',
            ],
        ]);

        $i->seeResponse(HttpCode::CREATED, [
            'data' => [
                'id' => 1,
                'status' => 'WAITING_FOR_PAYMENT',
                'start' => [
                    'latitude' => 46.42738,
                    'longitude' => 30.75128,
                    'address' => '7th st. Fontanskoyi dorohy',
                ],
                'end' => [
                    'latitude' => 46.42317,
                    'longitude' => 30.74705,
                    'address' => 'Sehedska Street, 5',
                ],
                'price' => [
                    'amount' => 6345,
                    'currency' => 'USD',
                ],
                'trip_time' => 68.3,
            ],
        ]);
    }

    public function nearestDriverCanReceiveAnOrder(ApiTester $i): void
    {
        $i->haveDriver(p(1));
        $i->moveToLocation(p(1), 46.43045, 30.75475); // 700m from the order start

        $i->haveDriver(p(2));
        $i->moveToLocation(p(2), 46.46266, 30.74391); // 4.4km from the order start

        $i->haveDriver(p(3));
        $i->moveToLocation(p(3), 46.42804, 30.74694); // 450m from the order start

        $user = $i->haveUser(p(4));
        $i->moveToLocation(p(4), 46.42738, 30.75128); // 0m from the order start

        $i->haveInRepository(TripOrder::class, [
            'id' => 1,
            'cost' => new Money(39540, 'USD'),
            'start' => new Location('7th st. Fontanskoyi dorohy', 46.4273814334286, 30.751279752912698),
            'end' => new Location('Sehedska Street, 5', 46.423173199108106, 30.74705368639186),
            'status' => TripStatus::WaitingForPayment,
            'paymentHoldId' => 'fake-payment-hold-id',
        ]);

        $i->grabService(EventDispatcherInterface::class)
            ->dispatch(new PaymentHeldForOrder($user->getId(), 1, 'fake-payment-hold-id'));

        $i->loginAs(p(1));
        $i->sendGetAsJson('/api/trip/orders');
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseEquals('{"data":[]}');

        $i->loginAs(p(2));
        $i->sendGetAsJson('/api/trip/orders');
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseEquals('{"data":[]}');

        $i->loginAs(p(3));
        $i->sendGetAsJson('/api/trip/orders');
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                [
                    'id' => 1,
                    'status' => 'WAITING_FOR_DRIVER',
                    'start' => [
                        'latitude' => 46.42738,
                        'longitude' => 30.75128,
                        'address' => '7th st. Fontanskoyi dorohy',
                    ],
                    'end' => [
                        'latitude' => 46.42317,
                        'longitude' => 30.74705,
                        'address' => 'Sehedska Street, 5',
                    ],
                    'trip_time' => 68.3,
                ],
            ],
        ]);
    }
}