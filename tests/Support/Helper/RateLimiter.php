<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Component\RateLimiter\TestStorage;

class RateLimiter extends \Codeception\Module
{
    public function markRateLimiterExceeded(): void
    {
        TestStorage::stopAccepting();
    }

    public function markRateLimiterAllowed(): void
    {
        TestStorage::startAccepting();
    }
}
