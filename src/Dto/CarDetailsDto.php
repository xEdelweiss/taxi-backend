<?php

namespace App\Dto;

readonly class CarDetailsDto
{
    public function __construct(
        public string $make,
        public string $model,
        public string $color,
        public string $license,
    ) {}
}
