<?php

namespace App\Dto\Trip;

use App\Service\Trip\Enum\TripStatusFilter;

readonly class ListOrdersQuery
{
    public function __construct(
        public TripStatusFilter $status,
    ) {}
}
