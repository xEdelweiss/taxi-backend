<?php

namespace App\Dto;

readonly class RouteDto
{
    public function __construct(
        public array $start,
        public array $finish,
        public float $distance,
        public float $duration,
        public string $polyline,
        public array $boundingBox,
    ) {}
}
