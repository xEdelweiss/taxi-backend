<?php

namespace App\Tests\Unit;

use Codeception\Test\Unit;

class HelpersTest extends Unit
{
    /** @test */
    public function pHelper(): void
    {
        $this->assertSame('380990000001', p('1'));
        $this->assertSame('380990123456', p('123456'));
        $this->assertSame('123456789012', p('123456789012'));
        $this->assertSame('380990000123', p(123));

        $this->expectException(\InvalidArgumentException::class);
        p('1234567890123');
    }
}
