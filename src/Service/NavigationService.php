<?php

namespace App\Service;

use App\Dto\RouteDto;
use App\Service\Navigation\RouterClientInterface;

class NavigationService
{
    public function __construct(
        private readonly RouterClientInterface $routerClient,
    ) {}

    public function calculateRoute(array $start, array $finish): RouteDto
    {
        return $this->routerClient->fetchRoute($start, $finish);
    }
}
