<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Entity\User;

class Auth extends \Codeception\Module
{
    /**
     * @fixme This works only for single login per test
     * @deprecated Use `loginAs` instead until this is fixed
     */
    public function amLoggedInAs(string|User $user, string $firewallName = 'main', string $firewallContext = null): User
    {
        $user = $this->ensureUserEntity($user);

        $this->getModule('Symfony')->amLoggedInAs($user, $firewallName, $firewallContext);

        return $user;
    }

    public function loginAs(string|User $user): User
    {
        $user = $this->ensureUserEntity($user);
        $rest = $this->getModule('REST');

        $rest->haveHttpHeader('Content-Type', 'application/json');
        $rest->haveHttpHeader('Accept', 'application/json');

        $rest
            ->sendPost('/api/auth/login', [
                'phone' => $user->getPhone(),
                'password' => '!password!',
            ]);

        $token = $rest->grabDataFromResponseByJsonPath('$.token')[0];

        $rest->haveHttpHeader('Authorization', "Bearer $token");

        return $user;
    }

    private function ensureUserEntity(User|string $user): User
    {
        return $user instanceof User
            ? $user
            : $this->getModule('Doctrine2')->grabEntityFromRepository(User::class, ['phone' => $user]);
    }
}
