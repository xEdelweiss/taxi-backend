<?php

namespace App\Service\Geolocation;

use App\Dto\AddressDto;
use App\Dto\CoordinatesDto;

class FakeGeocoder implements GeocoderInterface
{
    public function useLocale(string $locale): static
    {
        return $this;
    }

    public function coordinatesToAddress(float $latitude, float $longitude): AddressDto
    {
        return new AddressDto(
            'fake-' . substr($latitude, 0, 5) . '-' . substr($longitude, 0, 5),
        );
    }

    public function addressToCoordinates(string $address): CoordinatesDto
    {
        return new CoordinatesDto(
            '01.' . mb_strlen($address),
            '02.' . mb_strlen($address),
        );
    }
}
