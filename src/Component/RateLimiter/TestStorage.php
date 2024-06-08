<?php

namespace App\Component\RateLimiter;

use Symfony\Component\RateLimiter\LimiterStateInterface;
use Symfony\Component\RateLimiter\Policy\Window;
use Symfony\Component\RateLimiter\Storage\StorageInterface;

class TestStorage implements StorageInterface
{
    private static bool $accepting = true;

    public static function stopAccepting(): void
    {
        static::$accepting = false;
    }

    public static function startAccepting(): void
    {
        static::$accepting = true;
    }

    public function fetch(string $limiterStateId): ?LimiterStateInterface
    {
        if (static::$accepting) {
            return null;
        }

        return new Window($limiterStateId, 1, 0);
    }

    public function save(LimiterStateInterface $limiterState): void
    {
        // TODO: Implement save() method.
    }

    public function delete(string $limiterStateId): void
    {
        // TODO: Implement delete() method.
    }
}
