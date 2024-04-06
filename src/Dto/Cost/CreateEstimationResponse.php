<?php

namespace App\Dto\Cost;

use App\Dto\AbstractResponse;

readonly class CreateEstimationResponse extends AbstractResponse
{
    public function __construct(
        public float $cost,
        public string $currency,
    ) {}
}
