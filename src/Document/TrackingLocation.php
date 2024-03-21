<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class TrackingLocation
{
    #[ODM\Id]
    protected string $id;

    #[ODM\Field(type: "int")]
    #[ODM\Index(unique: true)]
    protected int $userId;

    #[ODM\EmbedOne(targetDocument: Coordinates::class)]
    protected $coordinates;

    public function __construct(int $userId, float $latitude, float $longitude)
    {
        $this->userId = $userId;
        $this->coordinates = new Coordinates($latitude, $longitude);
    }

    public function setCoordinates(float $latitude, float $longitude): void
    {
        if (!$this->coordinates->matches($latitude, $longitude)) {
            $this->coordinates = new Coordinates($latitude, $longitude);
        }
    }
}
