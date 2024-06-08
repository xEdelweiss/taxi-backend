<?php

namespace App\Service;

use App\Service\Geolocation\Geocoder\GeocoderInterface;
use App\Service\Geolocation\GeolocationServiceInterface;

readonly class GeolocationService implements GeolocationServiceInterface
{
    public function __construct(
        private GeocoderInterface $geocoder,
    ) {}

    public function getGeocoder(): GeocoderInterface
    {
        return $this->geocoder;
    }
}
