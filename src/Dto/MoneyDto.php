<?php

namespace App\Dto;

readonly class MoneyDto
{
    public function __construct(
        public int $amount,
        public string $currency
    ) {}
}
