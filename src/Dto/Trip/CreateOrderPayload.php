<?php

namespace App\Dto\Trip;

use App\Dto\LocationDto;

readonly class CreateOrderPayload
{
    public function __construct(
        public LocationDto $start,
        public LocationDto $end,
    ){ }
}
