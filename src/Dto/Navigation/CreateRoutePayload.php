<?php

namespace App\Dto\Navigation;

use App\Dto\LocationDto;

readonly class CreateRoutePayload
{
    public function __construct(
        public LocationDto $start,
        public LocationDto $end,
    ){ }
}
