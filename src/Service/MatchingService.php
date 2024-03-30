<?php

namespace App\Service;

use App\Entity\DriverProfile;
use App\Entity\Embeddable\Location;
use App\Service\Matching\MatchingStrategyInterface;

class MatchingService
{
    public function __construct(
        private readonly MatchingStrategyInterface $matchingStrategy,
    ) {}

    public function findMatchingDriver(Location $start): ?DriverProfile
    {
        return $this->matchingStrategy->findMatchingDriver($start);
    }
}
