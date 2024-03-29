<?php

namespace App\Service\Cost;

use App\Dto\RouteDto;

interface CostCalculatorInterface
{
    public function calculateCost(RouteDto $routeDto): float;
}
