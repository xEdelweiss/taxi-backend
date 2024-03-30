<?php

namespace App\Util;

class CoordinateUtils
{
    public function truncate(float|string $coordinate): float
    {
        return number_format($coordinate, 5, '.', '');
    }
}
