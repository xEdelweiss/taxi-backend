<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Document\TrackingLocation;
use App\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;

class Tracking extends \Codeception\Module
{
    public function moveToLocation(User|string $user, float $latitude, float $longitude): TrackingLocation
    {
        $user = $this->ensureUserEntity($user);

        /** @var DocumentManager $documentManager */
        $documentManager = $this->getModule('Symfony')->grabService(DocumentManager::class);
        $location = $documentManager->getRepository(TrackingLocation::class)
            ->findOneBy(['userId' => $user->getId()]);

        if ($location === null) {
            $location = new TrackingLocation($user->getId(), $latitude, $longitude, $user->isDriver() ? 'driver' : 'user');
            $documentManager->persist($location);
        } else {
            $location->setCoordinates($latitude, $longitude);
        }

        $documentManager->flush();

        return $location;
    }

    private function ensureUserEntity(User|string $user): User
    {
        return $user instanceof User
            ? $user
            : $this->getModule('Doctrine2')->grabEntityFromRepository(User::class, ['phone' => $user]);
    }
}
