<?php

namespace App\Dto;

use App\Entity\Embeddable\Money;

readonly class MoneyDto
{
    public function __construct(
        public float $amount,
        public string $currency,
    ) {}

    public static function fromEmbeddable(Money $money): self
    {
        return new self(
            amount: $money->getAmount(),
            currency: $money->getCurrency(),
        );
    }
}
