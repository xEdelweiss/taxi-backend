<?php

namespace App\Dto\Geolocation;

use App\Dto\AbstractResponse;
use App\Dto\AddressDto;

readonly class AddressResponse extends AbstractResponse
{
    public string $address;

    public function __construct(AddressDto $addressDto)
    {
        $this->address = $addressDto->address;
    }
}
