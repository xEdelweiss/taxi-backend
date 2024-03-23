<?php


namespace App\Tests\Acceptance;

use App\Tests\Support\AcceptanceTester;
use Codeception\Attribute\Examples;
use Codeception\Example;
use Codeception\Util\HttpCode;

class GeolocationCest
{
    #[Examples('uk-UA', 46.473957097700236, 30.744767085113498, 'вулиця Осипова, 27')]
    #[Examples('en-US', 46.4273814334286, 30.751279752912698, '7th st. Fontanskoyi dorohy')]
    public function canGetAddressByCoordinates(AcceptanceTester $i, Example $example): void
    {
        $i->amLoggedInAsNewUser(p(1));

        $i->haveHttpHeader('Accept-Language', $example[0]);
        $i->sendPostAsJson('/api/geolocation/addresses', [
            'latitude' => $example[1],
            'longitude' => $example[2],
        ]);

        $i->seeHttpHeader('Content-Language', explode('-', $example[0])[0]);
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'address' => $example[3],
            ],
        ]);
    }

    #[Examples('en-US', 'Osypova Street, 27', 46.474105550000004, 30.744822181966768)]
    #[Examples('uk-UA', '16 станція Фонтанської дороги', 46.3932621, 30.7471715)]
    public function canGetCoordinatesByAddress(AcceptanceTester $i, Example $example): void
    {
        $i->amLoggedInAsNewUser(p(1));

        $i->haveHttpHeader('Accept-Language', $example[0]);
        $i->sendPostAsJson('/api/geolocation/coordinates', [
            'address' => $example[1],
        ]);

        $i->seeHttpHeader('Content-Language', explode('-', $example[0])[0]);
        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'latitude' => $example[2],
                'longitude' => $example[3],
            ],
        ]);
    }
}
