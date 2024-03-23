<?php

namespace App\Entity;

use App\Dto\MoneyDto;
use App\Repository\TripOrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TripOrderRepository::class)]
class TripOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $status = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $cost = null;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private ?string $currency = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function setCost(MoneyDto $money): static
    {
        $this->cost = $money->amount;
        $this->currency = $money->currency;

        return $this;
    }

    public function getCost(): ?MoneyDto
    {
        if ($this->cost === null) {
            return null;
        }

        return new MoneyDto($this->cost, $this->currency);
    }
}
