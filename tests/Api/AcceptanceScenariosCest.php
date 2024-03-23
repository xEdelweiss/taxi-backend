<?php


namespace App\Tests\Api;

use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;

class AcceptanceScenariosCest
{
    public function userOrdersTaxi(ApiTester $i)
    {
        $i->haveUser(p(1));
        $i->haveDriver(p(2)); // driver

        // todo: user adds payment method

        // User: Moves to the location
        $i->loginAs(p(1));
        $i->sendPost('/api/tracking/locations', [
            'latitude' => 46.4273814334286,
            'longitude' => 30.751279752912698,
        ]);
        $i->seeResponseCodeIs(HttpCode::NO_CONTENT);

        // Driver: Moves to the location
        $i->loginAs(p(2));
        $i->sendPost('/api/tracking/locations', [
            'latitude' => 46.423173199108106,
            'longitude' => 30.74705368639186,
        ]);
        $i->seeResponseCodeIs(HttpCode::NO_CONTENT);

        // Driver: Starts working
        $i->sendPutAsJson('/api/driver/me', [
            'online' => true,
        ]);
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'id' => 2,
                'online' => true,
            ],
        ]);

        // User: Get start address by location
        $i->loginAs(p(1));
        $i->sendPostAsJson('/api/geolocation/addresses', [
            'latitude' => 46.4273814334286,
            'longitude' => 30.751279752912698,
        ]);
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'address' => 'fake-46.42-30.75',
            ],
        ]);

        // User: Get destination location by address
        $i->sendPostAsJson('/api/geolocation/coordinates', [
            'address' => 'Sehedska Street, 5'
        ]);
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'latitude' => 01.18,
                'longitude' => 02.18,
            ],
        ]);

        // User: Estimate cost
        $START_LOCATION = [
            'latitude' => 46.4273814334286,
            'longitude' => 30.751279752912698,
            'address' => '7th st. Fontanskoyi dorohy',
        ];
        $END_LOCATION = [
            'latitude' => 46.451538925795234,
            'longitude' => 30.743980453729417,
            'address' => 'Sehedska Street, 5',
        ];

        $i->sendPostAsJson('/api/cost/estimations', [
            'start' => $START_LOCATION,
            'end' => $END_LOCATION,
        ]);
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'price' => 100,
            ],
        ]);

        // User: Confirm order
        $i->sendPostAsJson('/api/trip/orders', [
            'start' => $START_LOCATION,
            'end' => $END_LOCATION
        ]);
        $i->seeResponse(HttpCode::CREATED, [
            'data' => [
                'id' => 1,
                'status' => 'WAITING_FOR_PAYMENT',
                'start' => $START_LOCATION,
                'end' => $END_LOCATION,
                'price' => [
                    'amount' => 100,
                    'currency' => 'USD',
                ],
                'driver_arrival_time' => 10,
                'trip_time' => 20,
            ],
        ]);

        // User: Hold payment for the order
        $i->sendPostAsJson('/api/payment/holds', [
            'order_id' => 1,
        ]);
        $i->seeResponseCodeIs(HttpCode::CREATED, [
            'data' => [
                'id' => 1,
                'order_id' => 1,
            ],
        ]);

        $i->sendGet('/api/payment/fake-provider?order_id=1');
        $i->seeResponseCodeIs(HttpCode::OK);

        // User: Poll for status
        $i->sendGetAsJson('/api/trip/orders/1');
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'id' => 1,
                'status' => 'WAITING_FOR_DRIVER',
            ],
        ]);

        // Driver: Get order
        $i->loginAs(p(2));
        $i->sendGetAsJson('/api/trip/orders');
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                [
                    'id' => 1,
                    'status' => 'WAITING_FOR_DRIVER',
                    'start' => $START_LOCATION,
                    'end' => $END_LOCATION,
                ],
            ],
        ]);

        // Driver: Accept order
        $i->sendPutAsJson('/api/trip/orders/1', [
            'status' => 'DRIVER_ON_WAY',
        ]);
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'id' => 1,
                'status' => 'DRIVER_ON_WAY',
            ],
        ]);

        // User: Poll for status
        $i->loginAs(p(1));
        $i->sendGetAsJson('/api/trip/orders/1');
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'id' => 1,
                'status' => 'DRIVER_ON_WAY',
            ],
        ]);

        // Driver: Arrived
        $i->loginAs(p(2));
        $i->sendPutAsJson('/api/trip/orders/1', [
            'status' => 'DRIVER_ARRIVED',
        ]);
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'id' => 1,
                'status' => 'DRIVER_ARRIVED',
            ],
        ]);

        // User: Poll for status
        $i->loginAs(p(1));
        $i->sendGetAsJson('/api/trip/orders/1');
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'id' => 1,
                'status' => 'DRIVER_ARRIVED',
            ],
        ]);

        // Driver: Start trip
        $i->loginAs(p(2));
        $i->sendPutAsJson('/api/trip/orders/1', [
            'status' => 'IN_PROGRESS',
        ]);

        // User and Driver arrive at the destination
        // User
        $i->loginAs(p(1));
        $i->sendPost('/api/tracking/locations', [
            'latitude' => 46.451535800869266,
            'longitude' => 30.743732579391782,
        ]);
        $i->seeResponseCodeIs(HttpCode::NO_CONTENT);
        // Driver
        $i->loginAs(p(2));
        $i->sendPost('/api/tracking/locations', [
            'latitude' => 46.451535800869266,
            'longitude' => 30.743732579391782,
        ]);
        $i->seeResponseCodeIs(HttpCode::NO_CONTENT);

        // Driver: End trip
        $i->sendPutAsJson('/api/trip/orders/1', [
            'status' => 'COMPLETED',
        ]);
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'id' => 1,
                'status' => 'COMPLETED',
            ],
        ]);

        // User: Poll for status
        $i->loginAs(p(1));
        $i->sendGetAsJson('/api/trip/orders/1');
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'id' => 1,
                'status' => 'COMPLETED',
            ],
        ]);

        // User: Rate driver
        $i->sendPostAsJson('/api/rating/records', [
            'order_id' => 1,
            'rating' => 5,
            'comment' => 'Great driver!',
        ]);
        $i->seeResponseCodeIs(HttpCode::CREATED, [
            'data' => [
                'id' => 1,
                'order_id' => 1,
                'rating' => 5,
                'comment' => 'Great driver!',
            ],
        ]);

        // Drive: Stop working
        $i->loginAs(p(2));
        $i->sendPutAsJson('/api/driver/me', [
            'online' => false,
        ]);
    }
}
