<?php

namespace App\Dto;

use App\Document\Coordinates;
use App\Entity\Embeddable\Location;
use App\Trait\CoordinateUtils;

readonly class LocationDto
{
    use CoordinateUtils;

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

    public static function fromCoordinates(Coordinates $coordinates): self
    {
        return new self(
            $coordinates->getLatitude(),
            $coordinates->getLongitude(),
            '',
        );
    }
}
