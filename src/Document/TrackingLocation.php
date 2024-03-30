<?php

namespace App\Document;

use App\Repository\TrackingLocationRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(repositoryClass: TrackingLocationRepository::class)]
#[ODM\Index(keys: ['coordinates' => '2dsphere'])]
class TrackingLocation
{
    #[ODM\Id]
    protected string $id;

    #[ODM\Field(type: "int")]
    #[ODM\Index(unique: true)]
    protected int $userId;

    #[ODM\EmbedOne(targetDocument: Coordinates::class)]
    protected $coordinates;

    #[ODM\Field(type: "string")]
    protected string $role;

    public function __construct(int $userId, float $latitude, float $longitude, string $role)
    {
        $this->userId = $userId;
        $this->coordinates = new Coordinates($latitude, $longitude);
        $this->role = $role;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCoordinates(): Coordinates
    {
        return $this->coordinates;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setCoordinates(float $latitude, float $longitude): static
    {
        if (!$this->coordinates->matches($latitude, $longitude)) {
            $this->coordinates = new Coordinates($latitude, $longitude);
        }

        return $this;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
