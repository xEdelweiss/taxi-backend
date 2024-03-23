<?php

namespace App\Tests\Support\Helper;

use Codeception\TestInterface;

class AcceptanceGuard extends \Codeception\Module
{
    public function _before(TestInterface $test)
    {
        $condition = $_ENV['ALLOW_ACCEPTANCE_TESTS'];
        $this->assertTrue($condition === 'true' || $condition === '1', 'Acceptance tests are disabled');
    }
}
