<?php

namespace App\Dto\Geolocation;

use App\Dto\AbstractResponse;
use App\Dto\CoordinatesDto;

readonly class CoordinatesResponse extends AbstractResponse
{
    public float $latitude;
    public float $longitude;

    public function __construct(CoordinatesDto $coordinates)
    {
        $this->latitude = $coordinates->latitude;
        $this->longitude = $coordinates->longitude;
    }
}
