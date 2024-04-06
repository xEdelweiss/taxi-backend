<?php

namespace App\Dto\Cost;

use App\Dto\LocationDto;

readonly class CreateEstimationPayload
{
    public function __construct(
        public LocationDto $start,
        public LocationDto $end,
    ){ }
}
