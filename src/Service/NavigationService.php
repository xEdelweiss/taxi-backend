<?php

namespace App\Service;

use App\Dto\LocationDto;
use App\Dto\RouteDto;
use App\Service\Navigation\Client\RouterClientInterface;

readonly class NavigationService
{
    public function __construct(
        private RouterClientInterface $routerClient,
    ) {}

    public function calculateRoute(LocationDto $start, LocationDto $finish): RouteDto
    {
        return $this->routerClient->fetchRoute($start, $finish);
    }
}
