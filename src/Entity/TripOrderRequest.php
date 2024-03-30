<?php

namespace App\Entity;

use App\Repository\TripOrderRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TripOrderRequestRepository::class)]
class TripOrderRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'tripOrderRequest', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private DriverProfile $driver;

    #[ORM\OneToOne(inversedBy: 'tripOrderRequest', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private TripOrder $tripOrder;

    public function __construct(DriverProfile $driver, TripOrder $tripOrder)
    {
        $this->driver = $driver;
        $this->tripOrder = $tripOrder;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDriver(): ?DriverProfile
    {
        return $this->driver;
    }

    public function getTripOrder(): ?TripOrder
    {
        return $this->tripOrder;
    }
}
