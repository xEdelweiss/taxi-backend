<?php


namespace App\Tests\Api;

use App\Entity\User;
use App\Tests\Support\ApiTester;
use Codeception\Attribute\Examples;
use Codeception\Example;
use Codeception\Util\HttpCode;

class DriverCest
{
    #[Examples(false, true)]
    #[Examples(true, false)]
    public function driverCanUpdateOnlineStatus(ApiTester $i, Example $example): void
    {
        $i->haveDriver(p(1), $example[0]);

        $i->loginAs(p(1));
        $i->sendPutAsJson('/api/driver/me', [
            'online' => $example[1],
        ]);

        $i->seeResponse(HttpCode::OK, [
            'id' => 1,
            'online' => $example[1],
        ]);

        $i->seeInRepository(User::class, [
            'id' => 1,
            'driverProfile' => [
                'online' => $example[1],
            ],
        ]);
    }
}
