<?php

namespace App\Entity;

use App\Repository\DriverProfileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DriverProfileRepository::class)]
class DriverProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private bool $online = false;

    #[ORM\OneToOne(mappedBy: 'driver', cascade: ['persist', 'remove'])]
    private ?TripOrderRequest $tripOrderRequest = null;

    #[ORM\OneToOne(mappedBy: 'driverProfile', cascade: ['persist', 'remove'])]
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): static
    {
        $this->online = $online;

        return $this;
    }

    public function getTripOrderRequest(): ?TripOrderRequest
    {
        return $this->tripOrderRequest;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
