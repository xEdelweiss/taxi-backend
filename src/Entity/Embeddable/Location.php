<?php

namespace App\Entity\Embeddable;

use App\Trait\CoordinateUtils;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Location
{
    use CoordinateUtils;

    #[ORM\Column(type: 'string', length: 255)]
    private string $address;

    #[ORM\Column(type: "decimal", precision: 8, scale: 5)]
    private float $latitude;

    #[ORM\Column(type: "decimal", precision: 8, scale: 5)]
    private float $longitude;

    public function __construct(string $address, float $latitude, float $longitude)
    {
        $this->address = $address;
        $this->setLatitude($latitude);
        $this->setLongitude($longitude);
    }

    public static function empty(): static
    {
        return new self('', 0.0, 0.0);
    }

    public static function fromArray(array $data): static
    {
        if (!isset($data['address'], $data['latitude'], $data['longitude'])) {
            throw new \InvalidArgumentException('Location data is invalid');
        }

        return self::empty()
            ->setAddress($data['address'])
            ->setLatitude($data['latitude'])
            ->setLongitude($data['longitude']);
    }

    public function isEmpty(): bool
    {
        return $this->address === '' && $this->latitude === 0.0 && $this->longitude === 0.0;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $this->truncateCoordinate($latitude);

        return $this;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $this->truncateCoordinate($longitude);

        return $this;
    }
}
