<?php

namespace App\Dto;

use App\Entity\User;

readonly class UserInfoDto
{
    public function __construct(
        public ?string $name,
        public string $phone,
    ) {}

    public static function fromUser(User $user): static
    {
        return new self(
            null,
            $user->getPhone(),
        );
    }
}
