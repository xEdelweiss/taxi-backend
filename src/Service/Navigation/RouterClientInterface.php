<?php

namespace App\Service\Navigation;

use App\Dto\RouteDto;

interface RouterClientInterface
{
    public function fetchRoute(array $start, array $finish): RouteDto;
}
