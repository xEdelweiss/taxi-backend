<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Entity\DriverProfile;
use App\Entity\Embeddable\Money;
use App\Entity\TripOrder;
use App\Entity\TripOrderRequest;
use App\Entity\User;
use App\Service\Trip\Enum\TripStatus;

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
            (new DriverProfile($user))
                ->setOnline($online)
        );

        $this->getModule('Doctrine2')->haveInRepository($user);

        return $user;
    }

    public function haveTripOrder(string|User $user, TripStatus $status): TripOrder
    {
        $tripOrder = new TripOrder($user);

        $reflectionClass = new \ReflectionClass(TripOrder::class);
        $reflectionProperty = $reflectionClass->getProperty('status');
        $reflectionProperty->setValue($tripOrder, $status);

        if (in_array($status, [
            TripStatus::WaitingForPayment,
            TripStatus::WaitingForDriver,
            TripStatus::DriverOnWay,
            TripStatus::DriverArrived,
            TripStatus::InProgress,
            TripStatus::Completed,
        ], true)) {
            $reflectionProperty = $reflectionClass->getProperty('cost');
            $reflectionProperty->setValue($tripOrder, new Money(100, 'USD'));
        }

        if (in_array($status, [
            TripStatus::WaitingForDriver,
            TripStatus::DriverOnWay,
            TripStatus::DriverArrived,
            TripStatus::InProgress,
            TripStatus::Completed,
        ], true)) {
            $reflectionProperty = $reflectionClass->getProperty('paymentHoldId');
            $reflectionProperty->setValue($tripOrder, 'fake-payment-hold-id');
        }

        if (in_array($status, [TripStatus::CanceledByDriver, TripStatus::CanceledByUser], true)) {
            $reflectionProperty = $reflectionClass->getProperty('paymentHoldId');
            $reflectionProperty->setValue($tripOrder, null);
        }

        $this->getModule('Doctrine2')->haveInRepository($tripOrder);

        return $tripOrder;
    }

    public function haveTripOrderWithDriver(string|User $user, TripStatus $status, string|User $driver): TripOrder
    {
        if ($status === TripStatus::Initial || $status === TripStatus::WaitingForPayment) {
            throw new \InvalidArgumentException(
                sprintf('Cannot assign driver to trip order with status "%s"', $status->value),
            );
        }

        $tripOrder = $this->haveTripOrder($user, $status);
        $driver = $this->ensureUserEntity($driver);

        if ($status->isAfter(TripStatus::WaitingForDriver, true)) {
            $tripOrderRequest = new TripOrderRequest($driver->getDriverProfile(), $tripOrder);
            $this->getModule('Doctrine2')->haveInRepository($tripOrderRequest);
        }

        return $tripOrder;
    }

    private function ensureUserEntity(User|string $user): User
    {
        return $user instanceof User
            ? $user
            : $this->getModule('Doctrine2')->grabEntityFromRepository(User::class, ['phone' => $user]);
    }
}
