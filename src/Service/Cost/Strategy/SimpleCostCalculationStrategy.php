<?php

namespace App\Service\Cost\Strategy;

use App\Dto\RouteDto;

class SimpleCostCalculationStrategy implements CostCalculationStrategyInterface
{
    public function calculateCost(RouteDto $routeDto): float
    {
        return $routeDto->distance * 10;
    }
}
