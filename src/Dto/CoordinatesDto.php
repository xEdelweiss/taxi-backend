<?php

namespace App\Dto;

use App\Document\TrackingLocation;

readonly class CoordinatesDto
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    public static function fromTrackingLocation(TrackingLocation $location): static
    {
        return new self(
            $location->getCoordinates()->getLatitude(),
            $location->getCoordinates()->getLongitude(),
        );
    }
}
