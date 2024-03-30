<?php

namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Location
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $address;

    #[ORM\Column(type: 'float')]
    private float $latitude;

    #[ORM\Column(type: 'float')]
    private float $longitude;

    public function __construct(string $address, float $latitude, float $longitude)
    {
        $this->address = $address;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
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

        return new self(
            $data['address'],
            $data['latitude'],
            $data['longitude']
        );
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
}
