<?php

namespace App\Service\Matching;

use App\Document\TrackingLocation;
use App\Entity\DriverProfile;
use App\Entity\Embeddable\Location;
use App\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;

class SimpleClosestMatchingStrategy implements MatchingStrategyInterface
{
    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function findMatchingDriver(Location $start): ?DriverProfile
    {
        $closestDriversLocations = $this->documentManager->getRepository(TrackingLocation::class)
            ->findClosestDrivers($start->getLatitude(), $start->getLongitude());

        if (empty($closestDriversLocations)) {
            return null;
        }

        return $this->entityManager
            ->find(User::class, $closestDriversLocations[0]->getUserId())
            ->getDriverProfile();
    }
}
