<?php


namespace Api;

use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;

class AuthCest
{
    public function programmaticalLoginWorksForUser(ApiTester $i)
    {
        $i->amLoggedInAsNewUser('380990000001');

        $i->sendGetAsJson('/api/auth/me');

        $i->seeResponse(HttpCode::OK, [
            'id' => 1,
            'phone' => '380990000001',
            'roles' => ['user'],
        ]);
        $i->dontSeeResponseContains('driver');
    }

    public function programmaticalLoginWorksForDriver(ApiTester $i)
    {
        $i->amLoggedInAsNewDriver('380990000001');

        $i->sendGetAsJson('/api/auth/me');

        $i->seeResponse(HttpCode::OK, [
            'id' => 1,
            'phone' => '380990000001',
            'roles' => ['user', 'driver'],
        ]);
    }

    public function userCanLogin(ApiTester $i): void
    {
        $i->haveUser('380990000001');

        $i->sendPostAsJson('/api/auth/login', [
            'phone' => '380990000001',
            'password' => '!password!',
        ]);

        $i->seeResponseContains('token');
        $i->haveHttpHeader('Authorization', 'Bearer ' . $i->grabDataFromResponseByJsonPath('$.token')[0]);
        $i->sendGetAsJson('/api/auth/me');
        $i->seeResponse(HttpCode::OK, [
            'id' => 1,
            'phone' => '380990000001',
            'roles' => ['user'],
        ]);
        $i->dontSeeResponseContains('driver');
    }

    public function driverCanLogin(ApiTester $i): void
    {
        $i->haveDriver('380990000001');

        $i->sendPostAsJson('/api/auth/login', [
            'phone' => '380990000001',
            'password' => '!password!',
        ]);

        $i->seeResponseContains('token');
        $i->haveHttpHeader('Authorization', 'Bearer ' . $i->grabDataFromResponseByJsonPath('$.token')[0]);
        $i->sendGetAsJson('/api/auth/me');
        $i->seeResponse(HttpCode::OK, [
            'id' => 1,
            'phone' => '380990000001',
            'roles' => ['user', 'driver'],
        ]);
    }
}
