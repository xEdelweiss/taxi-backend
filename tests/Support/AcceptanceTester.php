<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Codeception\Util\HttpCode;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends ApiTester
{
    use _generated\AcceptanceTesterActions;

    public function skipIfAcceptanceTestsDisabled(): void
    {
        $condition = $_ENV['ALLOW_ACCEPTANCE_TESTS'];

        if ($condition !== 'true' && $condition !== '1') {
            $this->markTestSkipped('Acceptance tests are disabled');
        }
    }

    public function sendRegisterRequest(string $phone, string $password = '!password!'): void
    {
        $this->sendPostAsJson('/api/auth/register', [
            'phone' => $phone,
            'password' => $password,
        ]);
        $this->seeResponseCodeIs(HttpCode::CREATED);
    }
}
