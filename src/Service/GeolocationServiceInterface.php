<?php

namespace App\Service;

use App\Service\Geolocation\GeocoderInterface;

interface GeolocationServiceInterface
{
    public function getGeocoder(): GeocoderInterface;
}
