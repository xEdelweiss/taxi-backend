<?php

namespace App\Dto;

readonly class CoordinatesDto
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}
}
