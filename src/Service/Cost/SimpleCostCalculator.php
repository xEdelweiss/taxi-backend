<?php

namespace App\Service\Cost;

use App\Dto\RouteDto;

class SimpleCostCalculator implements CostCalculatorInterface
{
    public function calculateCost(RouteDto $routeDto): float
    {
        return $routeDto->distance * 10;
    }
}
