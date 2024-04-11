<?php


namespace App\Tests\Api;

use App\Entity\Embeddable\Location;
use App\Entity\Embeddable\Money;
use App\Entity\TripOrder;
use App\Entity\TripOrderRequest;
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
            'finish' => [
                'latitude' => 46.423173199108106,
                'longitude' => 30.74705368639186,
                'address' => 'Sehedska Street, 5',
            ],
        ]);

        $i->seeResponse(HttpCode::CREATED, [
            'id' => 1,
            'status' => 'WAITING_FOR_PAYMENT',
            'start' => [
                'latitude' => 46.42738,
                'longitude' => 30.75128,
                'address' => '7th st. Fontanskoyi dorohy',
            ],
            'finish' => [
                'latitude' => 46.42317,
                'longitude' => 30.74705,
                'address' => 'Sehedska Street, 5',
            ],
            'route' => [
                'polyline' => '_zjzG}auzDh@URfATx@\~AJXp@jALPlB~A~@|@h@z@JRJNvAhAx@l@~BdBpC~ANq@',
                'bounding_box' => [
                    'bottom_left' => [
                        'latitude' => 46.423028,
                        'longitude' => 30.746708,
                    ],
                    'top_right' => [
                        'latitude' => 46.427359,
                        'longitude' => 30.751304,
                    ],
                ],
                'duration' => 68.3,
                'distance' => 634.5,
            ],
            'client' => [
                'name' => null,
                'phone' => '380990000001',
            ],
            'driver' => null,
            'driver_location' => null,
            'car' => null,
            'cost' => [
                'amount' => 6345,
                'currency' => 'USD',
            ],
            'start_eta' => 600,
            'finish_eta' => 668,
        ]);
    }

    public function useCanReadActiveOrders(ApiTester $i): void
    {
        // @fixme allow users to filter/fetch orders by status

        $user = $i->haveUser(p(1));

        $i->haveInRepository(TripOrder::class, [
            'id' => 1,
            'cost' => new Money(39540, 'USD'),
            'start' => new Location('7th st. Fontanskoyi dorohy', 46.4273814334286, 30.751279752912698),
            'end' => new Location('Sehedska Street, 5', 46.423173199108106, 30.74705368639186),
            'status' => TripStatus::WaitingForDriver,
            'paymentHoldId' => 'fake-payment-hold-id',
            'user' => $user,
        ]);

        $i->haveInRepository(TripOrder::class, [
            'id' => 2,
            'cost' => new Money(1234, 'USD'),
            'start' => new Location('Some address 1', 46.4273814334286, 30.751279752912698),
            'end' => new Location('Some address 2', 46.423173199108106, 30.74705368639186),
            'status' => TripStatus::Completed,
            'user' => $user,
        ]);

        $i->loginAs(p(1));
        $i->sendGetAsJson('/api/trip/orders');

        $i->seeResponse(HttpCode::OK, [
            'items' => [
                [
                    'id' => 1,
                    'status' => 'WAITING_FOR_DRIVER',
                    'start' => [
                        'latitude' => 46.42738,
                        'longitude' => 30.75128,
                        'address' => '7th st. Fontanskoyi dorohy',
                    ],
                    'finish' => [
                        'latitude' => 46.42317,
                        'longitude' => 30.74705,
                        'address' => 'Sehedska Street, 5',
                    ],
                    'client' => [
                        'name' => null,
                        'phone' => '380990000001',
                    ],
                    'driver' => null,
                    'car' => null,
                    'cost' => [
                        'amount' => 39540,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ]);
    }

    public function useCanReadSingleOrder(ApiTester $i): void
    {
        $user = $i->haveUser(p(1));

        $i->haveInRepository(TripOrder::class, [
            'id' => 1,
            'cost' => new Money(39540, 'USD'),
            'start' => new Location('7th st. Fontanskoyi dorohy', 46.4273814334286, 30.751279752912698),
            'end' => new Location('Sehedska Street, 5', 46.423173199108106, 30.74705368639186),
            'status' => TripStatus::WaitingForDriver,
            'paymentHoldId' => 'fake-payment-hold-id',
            'user' => $user,
        ]);

        $i->loginAs(p(1));
        $i->sendGetAsJson('/api/trip/orders/1');

        $i->seeResponse(HttpCode::OK, [
            'id' => 1,
            'status' => 'WAITING_FOR_DRIVER',
            'start' => [
                'latitude' => 46.42738,
                'longitude' => 30.75128,
                'address' => '7th st. Fontanskoyi dorohy',
            ],
            'finish' => [
                'latitude' => 46.42317,
                'longitude' => 30.74705,
                'address' => 'Sehedska Street, 5',
            ],
            'route' => [
                'polyline' => '_zjzG}auzDh@URfATx@\~AJXp@jALPlB~A~@|@h@z@JRJNvAhAx@l@~BdBpC~ANo@',
                'bounding_box' => [
                    'bottom_left' => [
                        'latitude' => 46.423029,
                        'longitude' => 30.746708,
                    ],
                    'top_right' => [
                        'latitude' => 46.427357,
                        'longitude' => 30.751304,
                    ],
                ],
                'duration' => 68.3,
                'distance' => 634.2,
            ],
            'client' => [
                'name' => null,
                'phone' => '380990000001',
            ],
            'driver' => null,
            'driver_location' => null,
            'car' => null,
            'cost' => [
                'amount' => 39540,
                'currency' => 'USD',
            ],
            'start_eta' => 600,
            'finish_eta' => 668,
        ]);
    }

    public function userCanCancelNonPaidOrder(ApiTester $i): void
    {
        $user = $i->haveUser(p(1));

        $i->haveInRepository(TripOrder::class, [
            'id' => 1,
            'cost' => new Money(39540, 'USD'),
            'start' => new Location('7th st. Fontanskoyi dorohy', 46.4273814334286, 30.751279752912698),
            'end' => new Location('Sehedska Street, 5', 46.423173199108106, 30.74705368639186),
            'status' => TripStatus::WaitingForPayment,
            'paymentHoldId' => null,
            'user' => $user,
        ]);

        $i->loginAs(p(1));
        $i->sendPutAsJson('/api/trip/orders/1', [
            'status' => 'CANCELED_BY_USER'
        ]);

        $i->seeResponse(HttpCode::OK, [
            'status' => 'CANCELED_BY_USER',
        ]);
        $i->seeInRepository(TripOrder::class, [
            'id' => 1,
            'status' => TripStatus::CanceledByUser,
        ]);
    }

    public function driverCanCancelNonPaidOrder(ApiTester $i): void
    {
        $user = $i->haveUser(p(1));
        $driver = $i->haveDriver(p(2));

        $i->haveInRepository(TripOrder::class, [
            'id' => 1,
            'cost' => new Money(39540, 'USD'),
            'start' => new Location('7th st. Fontanskoyi dorohy', 46.4273814334286, 30.751279752912698),
            'end' => new Location('Sehedska Street, 5', 46.423173199108106, 30.74705368639186),
            'status' => TripStatus::WaitingForPayment,
            'paymentHoldId' => null,
            'user' => $user,
        ]);

        $i->loginAs(p(2));
        $i->sendPutAsJson('/api/trip/orders/1', [
            'status' => 'CANCELED_BY_DRIVER'
        ]);

        $i->seeResponse(HttpCode::OK, [
            'status' => 'CANCELED_BY_DRIVER',
        ]);
        $i->seeInRepository(TripOrder::class, [
            'id' => 1,
            'status' => TripStatus::CanceledByDriver,
        ]);
    }

    public function orderRequestIsRemovedIfOrderIsCanceled(ApiTester $i): void
    {
        $user = $i->haveUser(p(1));
        $driver = $i->haveDriver(p(2));

        $orderId = $i->haveInRepository(TripOrder::class, [
            'id' => 1,
            'cost' => new Money(39540, 'USD'),
            'start' => new Location('7th st. Fontanskoyi dorohy', 46.4273814334286, 30.751279752912698),
            'end' => new Location('Sehedska Street, 5', 46.423173199108106, 30.74705368639186),
            'status' => TripStatus::WaitingForPayment,
            'paymentHoldId' => null,
            'user' => $user,
        ]);

        $tripOrder = new TripOrderRequest($driver->getDriverProfile(), $i->grabEntityFromRepository(TripOrder::class, ['id' => $orderId]));
        $i->haveInRepository($tripOrder);

        $i->loginAs(p(2));
        $i->sendPutAsJson('/api/trip/orders/1', [
            'status' => 'CANCELED_BY_DRIVER'
        ]);

        $i->seeInRepository(TripOrder::class, [
            'id' => 1,
            'status' => TripStatus::CanceledByDriver,
        ]);

        $i->dontSeeInRepository(TripOrderRequest::class, [
            'id' => 1,
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
            'user' => $user,
        ]);

        $i->grabService(EventDispatcherInterface::class)
            ->dispatch(new PaymentHeldForOrder($user->getId(), 1, 'fake-payment-hold-id'));

        $i->loginAs(p(1));
        $i->sendGetAsJson('/api/trip/orders');
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseEquals('{"items":[]}');

        $i->loginAs(p(2));
        $i->sendGetAsJson('/api/trip/orders');
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseEquals('{"items":[]}');

        $i->loginAs(p(3));
        $i->sendGetAsJson('/api/trip/orders');
        $i->seeResponse(HttpCode::OK, [
            'items' => [
                [
                    'id' => 1,
                    'status' => 'WAITING_FOR_DRIVER',
                    'start' => [
                        'latitude' => 46.42738,
                        'longitude' => 30.75128,
                        'address' => '7th st. Fontanskoyi dorohy',
                    ],
                    'finish' => [
                        'latitude' => 46.42317,
                        'longitude' => 30.74705,
                        'address' => 'Sehedska Street, 5',
                    ],
                    'client' => [
                        'name' => null,
                        'phone' => '380990000004',
                    ],
                    'driver' => [
                        'name' => null,
                        'phone' => '380990000003',
                    ],
                    'car' => null,
                    'cost' => [
                        'amount' => 39540,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ]);
    }
}
