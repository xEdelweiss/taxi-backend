<?php

namespace App\Event;

readonly class UserRegistered
{
    public function __construct(
        public int $userId
    ) {}
}
