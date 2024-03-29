<?php

namespace App\Service;

use App\Service\Geolocation\GeocoderInterface;
use App\Service\Geolocation\GeolocationServiceInterface;

class GeolocationService implements GeolocationServiceInterface
{
    public function __construct(
        private GeocoderInterface $geocoder,
    ) {}

    public function getGeocoder(): GeocoderInterface
    {
        return $this->geocoder;
    }
}