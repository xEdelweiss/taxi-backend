<?php

namespace App\Dto\Navigation;

use App\Dto\AbstractResponse;
use App\Dto\BoundingBoxDto;
use App\Dto\RouteDto;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class RouteResponse extends AbstractResponse
{
    public float $distance;
    public float $duration;
    public string $polyline;

    #[SerializedName('bounding_box')]
    public BoundingBoxDto $boundingBox;

    public function __construct(RouteDto $route)
    {
        $this->distance = $route->distance;
        $this->duration = $route->duration;
        $this->polyline = $route->polyline;
        $this->boundingBox = $route->boundingBox;
    }
}
