<?php

namespace App\Dto\Driver;

readonly class UpdateDriverPayload
{
    public function __construct(
        public bool $online
    ) {}
}
