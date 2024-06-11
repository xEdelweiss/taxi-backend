<?php

namespace App\Service\Navigation\Client;

use App\Dto\LocationDto;
use App\Dto\RouteDto;

interface RouterClientInterface
{
    public function fetchRoute(LocationDto $start, LocationDto $finish): RouteDto;
}
