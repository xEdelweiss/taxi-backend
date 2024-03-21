<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Entity\DriverProfile;
use App\Entity\User;

class Entities extends \Codeception\Module
{
    public function haveUser(string $phone): User
    {
        $user = new User($phone);
        $user->setPassword('$2y$13$aWqkhhvxijEHLqvhf2eoPulxi74ewNAJCSDpHTeNemoJ/6y/jXqH.'); // !password!

        $this->getModule('Doctrine2')->haveInRepository($user);

        return $user;
    }

    public function haveDriver(string $phone, bool $online = true): User
    {
        $user = new User($phone);
        $user->setPassword('$2y$13$aWqkhhvxijEHLqvhf2eoPulxi74ewNAJCSDpHTeNemoJ/6y/jXqH.'); // !password!
        $user->setDriverProfile(
            (new DriverProfile())
                ->setOnline($online)
        );

        $this->getModule('Doctrine2')->haveInRepository($user);

        return $user;
    }
}
