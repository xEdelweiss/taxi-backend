<?php

namespace App\Document;

use App\Trait\CoordinateUtils;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\EmbeddedDocument]
class Coordinates
{
    use CoordinateUtils;

    #[ODM\Field(type: "float")]
    protected float $longitude;

    #[ODM\Field(type: "float")]
    protected float $latitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $this->truncateCoordinate($latitude);
        $this->longitude = $this->truncateCoordinate($longitude);
    }

    public function matches(float $latitude, float $longitude): bool
    {
        return $this->latitude === $this->truncateCoordinate($latitude)
            && $this->longitude === $this->truncateCoordinate($longitude);
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }
}
