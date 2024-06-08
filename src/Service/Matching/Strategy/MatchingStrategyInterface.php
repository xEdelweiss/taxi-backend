<?php

namespace App\Service\Matching\Strategy;

use App\Entity\DriverProfile;
use App\Entity\Embeddable\Location;

interface MatchingStrategyInterface
{
    public function findMatchingDriver(Location $start): ?DriverProfile;
}
