<?php

namespace App\Dto\Driver;

use App\Dto\AbstractResponse;
use App\Entity\User;

readonly class DriverResponse extends AbstractResponse
{
    public int $id;
    public bool $online;

    public function __construct(User $user)
    {
        $this->id = $user->getId();
        $this->online = $user->getDriverProfile()->isOnline();
    }
}
