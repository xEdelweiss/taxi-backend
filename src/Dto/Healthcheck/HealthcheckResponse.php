<?php

namespace App\Dto\Healthcheck;

readonly class HealthcheckResponse
{
    public function __construct(
        public string $message,
    ) {}
}
