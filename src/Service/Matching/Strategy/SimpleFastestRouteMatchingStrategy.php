<?php

namespace App\Service\Matching\Strategy;

use App\Document\TrackingLocation;
use App\Dto\LocationDto;
use App\Entity\DriverProfile;
use App\Entity\Embeddable\Location;
use App\Repository\TrackingLocationRepository;
use App\Repository\UserRepository;
use App\Service\NavigationService;

readonly class SimpleFastestRouteMatchingStrategy implements MatchingStrategyInterface
{
    public function __construct(
        private TrackingLocationRepository $trackingLocationRepository,
        private UserRepository             $userRepository,
        private NavigationService          $navigationService,
    ) {}

    public function findMatchingDriver(Location $start): ?DriverProfile
    {
        $closestDriversLocations = $this->trackingLocationRepository
            ->findClosestDrivers($start->getLatitude(), $start->getLongitude());

        if (empty($closestDriversLocations)) {
            return null;
        }

        $shortestDriverLocation = $this->getFastestRoute($closestDriversLocations, $start);

        if ($shortestDriverLocation === null) {
            throw new \LogicException('No shortest driver found, but there should be one.');
        }

        return $this->userRepository
            ->find($shortestDriverLocation->getUserId())
            ->getDriverProfile();
    }

    /** @param TrackingLocation[] $closestDriversLocations */
    private function getFastestRoute(array $closestDriversLocations, Location $start): ?TrackingLocation
    {
        $shortestDuration = PHP_INT_MAX;
        $shortestDriverLocation = null;

        foreach ($closestDriversLocations as $location) {
            $route = $this->navigationService->calculateRoute(
                LocationDto::fromEmbeddable($start),
                LocationDto::fromCoordinates($location->getCoordinates()),
            );

            if ($route->duration < $shortestDuration) {
                $shortestDuration = $route->duration;
                $shortestDriverLocation = $location;
            }
        }

        return $shortestDriverLocation;
    }
}
