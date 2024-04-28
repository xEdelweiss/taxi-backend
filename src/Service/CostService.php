<?php

namespace App\Service;

use App\Dto\RouteDto;
use App\Service\Cost\CostCalculatorInterface;

readonly class CostService
{
    public function __construct(
        private CostCalculatorInterface $costCalculator,
    ) {}

    public function calculateCost(RouteDto $routeDto): float
    {
        return $this->costCalculator->calculateCost($routeDto);
    }
}
