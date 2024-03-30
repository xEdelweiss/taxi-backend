<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Responses extends \Codeception\Module
{
    public function seeResponse(int $code, array $json = []): void
    {
        $rest = $this->getModule('REST');

        $rest->seeResponseCodeIs($code);

        if ($json !== []) {
            $rest->seeResponseContainsJson($json);
        }
    }
}
