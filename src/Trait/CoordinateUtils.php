<?php

namespace App\Trait;

trait CoordinateUtils
{
    private function truncateCoordinate(float|string $coordinate): float
    {
        return (new \App\Util\CoordinateUtils())
            ->truncate($coordinate);
    }
}
