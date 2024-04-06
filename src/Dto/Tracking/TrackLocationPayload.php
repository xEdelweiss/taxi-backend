<?php

namespace App\Dto\Tracking;

readonly class TrackLocationPayload
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}
}
