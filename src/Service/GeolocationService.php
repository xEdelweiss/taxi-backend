<?php

namespace App\Service;

use App\Service\Geolocation\GeocoderInterface;

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
