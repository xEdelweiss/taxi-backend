<?php

namespace App\Service\Matching;

use App\Entity\DriverProfile;
use App\Entity\Embeddable\Location;
use App\Repository\TrackingLocationRepository;
use App\Repository\UserRepository;

readonly class SimpleClosestMatchingStrategy implements MatchingStrategyInterface
{
    public function __construct(
        private TrackingLocationRepository $trackingLocationRepository,
        private UserRepository             $userRepository,
    ) {}

    public function findMatchingDriver(Location $start): ?DriverProfile
    {
        $closestDriversLocations = $this->trackingLocationRepository
            ->findClosestDrivers($start->getLatitude(), $start->getLongitude());

        if (empty($closestDriversLocations)) {
            return null;
        }

        return $this->userRepository
            ->find($closestDriversLocations[0]->getUserId())
            ->getDriverProfile();
    }
}
