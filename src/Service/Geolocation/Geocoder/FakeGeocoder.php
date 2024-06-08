<?php

namespace App\Service\Geolocation\Geocoder;

use App\Dto\AddressDto;
use App\Dto\CoordinatesDto;
use App\Exception\Geolocation\AddressNotFound;

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
        if ($address === 'fake address') {
            throw new AddressNotFound($address);
        }

        return new CoordinatesDto(
            '01.' . mb_strlen($address),
            '02.' . mb_strlen($address),
        );
    }
}
