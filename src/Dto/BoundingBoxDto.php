<?php

namespace App\Dto;

class BoundingBoxDto
{
    public function __construct(
        public CoordinatesDto $bottomLeft,
        public CoordinatesDto $topRight
    ) {}

    public static function fromArray(array $bbox): self
    {
        return new self(
            new CoordinatesDto($bbox['1'], $bbox[0]),
            new CoordinatesDto($bbox['3'], $bbox[2])
        );
    }
}
