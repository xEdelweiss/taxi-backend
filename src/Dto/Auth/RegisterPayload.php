<?php

namespace App\Dto\Auth;
use Symfony\Component\Validator\Constraints as Assert;

readonly class RegisterPayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'Name is required.')]
        public string $phone,

        #[Assert\NotBlank(message: 'Password is required.')]
        public string $password,
    ) {}
}
