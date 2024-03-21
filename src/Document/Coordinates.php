<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\EmbeddedDocument]
class Coordinates
{
    #[ODM\Field(type: "float")]
    protected float $longitude;

    #[ODM\Field(type: "float")]
    protected float $latitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function matches(float $latitude, float $longitude): bool
    {
        return $this->latitude === $latitude && $this->longitude === $longitude;
    }
}
