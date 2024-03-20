<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Entity\User;

class Entities extends \Codeception\Module
{
    public function haveUser(string $phone): User
    {
        $user = new User();
        $user->setPhone($phone);
        $user->setPassword('!password!');

        $this->getModule('Doctrine2')->haveInRepository($user);

        return $user;
    }
}
