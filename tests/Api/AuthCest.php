<?php


namespace Api;

use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;

class AuthCest
{
    public function itWorks(ApiTester $I)
    {
        $I->amLoggedInAsNew('380990000001');

        $I->sendGet('/api/auth/me');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id' => 1,
            'phone' => '380990000001',
        ]);
    }
}
