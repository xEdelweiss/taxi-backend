<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class MinimalRouteDto
{
    public function __construct(
        public float          $distance,
        public float          $duration,
        public string         $polyline,

        #[SerializedName('bounding_box')]
        public BoundingBoxDto $boundingBox,
    ) {}

    public static function fromRouteDto(RouteDto $routeDto): static
    {
        return new self(
            $routeDto->distance,
            $routeDto->duration,
            $routeDto->polyline,
            $routeDto->boundingBox,
        );
    }
}
