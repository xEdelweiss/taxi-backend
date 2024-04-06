<?php

namespace App\Dto\Auth;

use App\Entity\User;

readonly class CurrentUserResponse
{
    public int $id;

    public string $phone;

    /** @var string[] */
    public array $roles;

    public function __construct(User $user)
    {
        $this->id = $user->getId();
        $this->phone = $user->getPhone();
        $this->roles = $this->serializeRoles($user);
    }

    private function serializeRoles(User $user): array
    {
        return array_filter(array_map(
            fn($role) => match ($role) {
                'ROLE_USER' => 'user',
                'ROLE_DRIVER' => 'driver',
                default => null,
            },

            $user->getRoles()
        ));
    }
}
