<?php

namespace App\Dto;

use App\Entity\Embeddable\Location;

readonly class LocationDto
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public string $address,
    ) {}

    public static function fromEmbeddable(Location $location): self
    {
        return new self(
            $location->getLatitude(),
            $location->getLongitude(),
            $location->getAddress(),
        );
    }

    public function toLatLng(): array
    {
        return [$this->latitude, $this->longitude];
    }
}
