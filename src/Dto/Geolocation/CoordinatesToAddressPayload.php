<?php

namespace App\Dto\Geolocation;

readonly class CoordinatesToAddressPayload
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}
}
