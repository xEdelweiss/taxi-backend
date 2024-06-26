<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Entity\User;

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
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    public function amLoggedInAsNewUser(string $phone = '380990000001'): User
    {
        $user = $this->haveUser($phone);
        $this->loginAs($user);

        return $user;
    }

    public function amLoggedInAsNewDriver(string $phone = '380990000001'): User
    {
        $user = $this->haveDriver($phone);
        $this->loginAs($user);

        return $user;
    }

    public function makeAddPaymentMethodsRequestAsUser(string $phone): User
    {
        $user = $this->haveUser($phone);
        $this->linkPaymentAccountId($phone);
        $this->loginAs($phone);
        $this->sendPostAsJson('/api/payment/payment-methods', [
            'return_url' => 'http://localhost/fake-app-redirect',
        ]);

        return $user;
    }
}
