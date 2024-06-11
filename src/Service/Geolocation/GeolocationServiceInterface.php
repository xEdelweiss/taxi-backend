<?php

namespace App\Service\Geolocation;

use App\Service\Geolocation\Geocoder\GeocoderInterface;

interface GeolocationServiceInterface
{
    public function getGeocoder(): GeocoderInterface;
}
