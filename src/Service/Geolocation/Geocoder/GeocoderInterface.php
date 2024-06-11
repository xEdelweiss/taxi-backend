<?php

namespace App\Service\Geolocation\Geocoder;

use App\Dto\AddressDto;
use App\Dto\CoordinatesDto;

interface GeocoderInterface
{
    public function useLocale(string $locale): static;
    public function coordinatesToAddress(float $latitude, float $longitude): AddressDto;
    public function addressToCoordinates(string $address): CoordinatesDto;
}
