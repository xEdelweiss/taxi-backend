<?php

namespace App\Service\Geolocation;

use App\Service\Geolocation\GeocoderInterface;

interface GeolocationServiceInterface
{
    public function getGeocoder(): GeocoderInterface;
}
