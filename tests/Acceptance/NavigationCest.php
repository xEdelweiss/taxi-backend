<?php


namespace App\Tests\Acceptance;

use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Attributes\Test;

class NavigationCest
{
    #[Test]
    public function canCalculateRouteBetweenPoints(ApiTester $i): void
    {
        $i->amLoggedInAsNewDriver(p(1));

        $i->sendPostAsJson('/api/navigation/routes', [
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

        $i->seeResponse(HttpCode::OK, [
            'distance' => 634.5,
            'duration' => 68.3,
        ]);
    }

    public function calculatedRouteContainsEncodedPolyline(ApiTester $i): void
    {
        $i->amLoggedInAsNewDriver(p(1));

        $i->sendPostAsJson('/api/navigation/routes', [
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

        $i->seeResponse(HttpCode::OK);
        $i->assertIsString(
            $i->grabDataFromResponseByJsonPath('$.polyline')[0]
        );
    }

    public function calculatedRouteContainsBoundingBox(ApiTester $i): void
    {
        $i->amLoggedInAsNewDriver(p(1));

        $i->sendPostAsJson('/api/navigation/routes', [
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

        $i->seeResponse(HttpCode::OK, [
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
        ]);
    }
}
