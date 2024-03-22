<?php


namespace App\Tests\Api;

use App\Tests\Support\ApiTester;
use Codeception\Attribute\Env;
use Codeception\Attribute\Examples;
use Codeception\Example;
use Codeception\Util\HttpCode;

class GeolocationCest
{
    #[Env('staging')]
    #[Examples('uk-UA', 46.473957097700236, 30.744767085113498, 'вулиця Осипова, 27')]
    #[Examples('en-US', 46.4273814334286, 30.751279752912698, '7th st. Fontanskoyi dorohy')]
    public function canGetAddressByCoordinatesByRealProvider(ApiTester $i, Example $example): void
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

    #[Env('staging')]
    #[Examples('en-US', 'Osypova Street, 27', 46.474105550000004, 30.744822181966768)]
    #[Examples('uk-UA', '16 станція Фонтанської дороги', 46.392681, 30.7472633)]
    public function canGetCoordinatesByAddressByRealProvider(ApiTester $i, Example $example): void
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

    #[Examples(46.4273814334286, 30.751279752912698, 'fake-46.42-30.75')]
    #[Examples(46.473957097700236, 30.744767085113498, 'fake-46.47-30.74')]
    public function canGetAddressByCoordinates(ApiTester $i, Example $example): void
    {
        $i->amLoggedInAsNewUser(p(1));

        $i->sendPostAsJson('/api/geolocation/addresses', [
            'latitude' => $example[0],
            'longitude' => $example[1],
        ]);

        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'address' => $example[2],
            ],
        ]);
    }

    #[Examples('Osypova Street, 27', 01.18, 02.18)]
    #[Examples('16th st. Fontanskoyi dorohy', 01.27, 02.27)]
    public function canGetCoordinatesByAddress(ApiTester $i, Example $example): void
    {
        $i->amLoggedInAsNewUser(p(1));

        $i->sendPostAsJson('/api/geolocation/coordinates', [
            'address' => $example[0],
        ]);

        $i->seeResponse(HttpCode::OK, [
            'data' => [
                'latitude' => $example[1],
                'longitude' => $example[2],
            ],
        ]);
    }
}
