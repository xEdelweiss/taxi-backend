<?php

namespace App\Dto\Geolocation;

readonly class AddressToCoordinatesPayload
{
    public function __construct(
        public string $address,
    ) {}
}
