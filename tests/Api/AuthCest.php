<?php


namespace Api;

use App\Entity\User;
use App\Event\UserRegistered;
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

    public function userCanRegister(ApiTester $i): void
    {
        $i->sendPostAsJson('/api/auth/register', [
            'phone' => p(1),
            'password' => '!password!',
        ]);

        $i->seeResponse(HttpCode::CREATED, [
            'message' => 'Account created.'
        ]);
        $i->seeEvent(UserRegistered::class);
        $i->seeInRepository(User::class, [
            'id' => 1,
            'phone' => p(1),
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
