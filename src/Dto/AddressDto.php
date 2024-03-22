<?php

namespace App\Dto;

readonly class AddressDto
{
    public function __construct(
        public string $address,
    ) {}
}
