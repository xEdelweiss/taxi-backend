<?php

namespace App\Service\Cost\Strategy;

use App\Dto\RouteDto;

interface CostCalculationStrategyInterface
{
    public function calculateCost(RouteDto $routeDto): float;
}
