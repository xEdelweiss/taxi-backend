<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

class BoundingBoxDto
{
    public function __construct(
        #[SerializedName('bottom_left')]
        public CoordinatesDto $bottomLeft,

        #[SerializedName('top_right')]
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
