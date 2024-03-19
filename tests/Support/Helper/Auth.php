<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Entity\User;
use App\Entity\Workspace;
use App\Enum\MembershipStatus;

class Auth extends \Codeception\Module
{
    public function amLoggedInAs(string|User $user, string $firewallName = 'main', string $firewallContext = null): User
    {
        $user = $user instanceof User
            ? $user
            : $this->getModule('Doctrine2')->grabEntityFromRepository(User::class, ['phone' => $user]);

        $this->getModule('Symfony')->amLoggedInAs($user, $firewallName, $firewallContext);

        return $user;
    }
}
