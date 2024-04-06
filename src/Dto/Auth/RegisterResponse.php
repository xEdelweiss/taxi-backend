<?php

namespace App\Dto\Auth;

readonly class RegisterResponse
{
    public function __construct(
        public string $message = 'Account created.',
    ) {}
}
