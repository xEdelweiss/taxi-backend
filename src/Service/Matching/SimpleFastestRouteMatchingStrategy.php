<?php

namespace App\Service\Matching;

use App\Document\TrackingLocation;
use App\Dto\LocationDto;
use App\Entity\DriverProfile;
use App\Entity\Embeddable\Location;
use App\Entity\User;
use App\Service\NavigationService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;

class SimpleFastestRouteMatchingStrategy implements MatchingStrategyInterface
{
    public function __construct(
        private readonly DocumentManager        $documentManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly NavigationService      $navigationService,
    ) {}

    public function findMatchingDriver(Location $start): ?DriverProfile
    {
        $closestDriversLocations = $this->documentManager->getRepository(TrackingLocation::class)
            ->findClosestDrivers($start->getLatitude(), $start->getLongitude());

        if (empty($closestDriversLocations)) {
            return null;
        }

        $shortestDriverLocation = $this->getFastestRoute($closestDriversLocations, $start);

        if ($shortestDriverLocation === null) {
            throw new \LogicException('No shortest driver found, but there should be one.');
        }

        return $this->entityManager
            ->find(User::class, $shortestDriverLocation->getUserId())
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
