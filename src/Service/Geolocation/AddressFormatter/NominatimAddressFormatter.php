<?php

namespace App\Service\Geolocation\AddressFormatter;

class NominatimAddressFormatter
{
    public function format(array $reverseResponse): string
    {
        if (isset($reverseResponse['address']['railway'])) {
            return $reverseResponse['address']['railway'];
        }

        if (isset($reverseResponse['name']) && $reverseResponse['name'] !== '') {
            return $reverseResponse['name'];
        }

        if (isset($reverseResponse['address']['road'], $reverseResponse['address']['house_number'])) {
            return $reverseResponse['address']['road'] . ', ' . $reverseResponse['address']['house_number'];
        }

        if (isset($reverseResponse['address']['road'])) {
            return $reverseResponse['address']['road'];
        }

        return '';
    }
}
