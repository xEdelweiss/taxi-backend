<?php

namespace App\Dto\Navigation;

use App\Dto\AbstractResponse;
use App\Dto\RouteDto;

readonly class RouteResponse extends AbstractResponse
{
    public float $distance;
    public float $duration;

    public function __construct(RouteDto $route)
    {
        $this->distance = $route->distance;
        $this->duration = $route->duration;
    }
}
