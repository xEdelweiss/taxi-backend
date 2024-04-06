<?php

namespace App\Dto\Trip;

use App\Service\Trip\Enum\TripStatus;

readonly class UpdateOrderPayload
{
    public function __construct(
        public TripStatus $status,
    ) {}
}
