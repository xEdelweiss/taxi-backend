<?php

namespace App\Trait;

trait CoordinateUtils
{
    private function truncateCoordinate(float|string $coordinate): float
    {
        return (new \App\Util\CoordinateUtils())
            ->truncate($coordinate);
    }

    public function toLatLng(): array
    {
        return [$this->latitude, $this->longitude];
    }
}
