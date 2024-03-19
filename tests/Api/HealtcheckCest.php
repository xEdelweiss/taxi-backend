<?php


namespace App\Tests\Api;

use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;

class HealtcheckCest
{
    public function itWorks(ApiTester $I)
    {
        $I->sendGet('/api/healthcheck');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Working.',
        ]);
    }
}
