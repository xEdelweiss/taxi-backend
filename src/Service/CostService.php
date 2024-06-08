<?php

namespace App\Service;

use App\Dto\RouteDto;
use App\Service\Cost\Strategy\CostCalculationStrategyInterface;

readonly class CostService
{
    public function __construct(
        private CostCalculationStrategyInterface $calculationStrategy,
    ) {}

    public function calculateCost(RouteDto $routeDto): float
    {
        return $this->calculationStrategy->calculateCost($routeDto);
    }
}
