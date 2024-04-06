<?php


namespace App\Tests\Api;

use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Attributes\Test;

class CostCest
{
    #[Test]
    public function canEstimateCostByStartAndFinishLocations(ApiTester $i): void
    {
        $i->amLoggedInAsNewUser(p(1));

        $i->sendPostAsJson('/api/cost/estimations', [
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
            'cost' => 634.5 * 10,
            'currency' => 'USD',
        ]);
    }
}
