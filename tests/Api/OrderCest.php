<?php


namespace App\Tests\Api;

use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;

class OrderCest
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
                    'latitude' => 46.4273814334286,
                    'longitude' => 30.751279752912698,
                    'address' => '7th st. Fontanskoyi dorohy',
                ],
                'end' => [
                    'latitude' => 46.423173199108106,
                    'longitude' => 30.74705368639186,
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
}
