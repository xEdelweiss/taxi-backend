<?php

namespace App\Tests\Support\Helper;

use Codeception\Module\Symfony;
use Codeception\TestInterface;

class MigrateDb extends \Codeception\Module
{
    public function _before(TestInterface $test)
    {
        /** @var Symfony $symfony */
        $symfony = $this->getModule('Symfony');

        $symfony->runSymfonyConsoleCommand('doctrine:schema:update', ['--force' => true, '--env' => 'test']);
    }
}
