<?php

namespace App\Service;

use App\Dto\RouteDto;
use App\Service\Cost\CostCalculatorInterface;

class CostService
{
    public function __construct(
        private readonly CostCalculatorInterface $costCalculator,
    ) {}

    public function calculateCost(RouteDto $routeDto): float
    {
        return $this->costCalculator->calculateCost($routeDto);
    }
}
