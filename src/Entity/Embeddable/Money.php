<?php

namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Money
{
    #[ORM\Column(type: 'integer', nullable: true)]
    private int $amount;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private string $currency;

    public function __construct(int $amount, string $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function empty(): static
    {
        return new self(0, 'USD');
    }

    public function isEmpty(): bool
    {
        return $this->amount === 0;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
