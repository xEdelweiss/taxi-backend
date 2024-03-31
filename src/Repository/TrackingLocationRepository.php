<?php

namespace App\Repository;

use App\Document\TrackingLocation;
use App\Entity\User;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class TrackingLocationRepository extends DocumentRepository
{
    /** @return TrackingLocation[] */
    public function findClosestDrivers(float $latitude, float $longitude, int $maxDistance = 1000, int $limit = 5): array
    {
        return $this->findBy([
            'role' => 'driver',
            'coordinates' => [
                '$near' => [
                    '$maxDistance' => $maxDistance, // in meters
                    '$geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            // IMPORTANT: GeoJSON format is [longitude, latitude]
                            $longitude,
                            $latitude,
                        ],
                    ],
                ],
            ],
        ], limit: $limit);
    }

    public function findByUser(User $user): ?TrackingLocation
    {
        return $this->findOneBy(['userId' => $user->getId()]);
    }
}
