<?php

namespace App\Dto;

readonly class RouteDto
{
    public function __construct(
        public LocationDto $start,
        public LocationDto $finish,
        public float $distance,
        public float $duration,
        public string $polyline,
        public BoundingBoxDto $boundingBox,
    ) {}
}
