<?php

namespace App\Tests\Unit\Document;

use App\Document\Coordinates;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\Test;

class CoordinatesTest extends Unit
{
    #[Test]
    public function trimsCoordinates(): void
    {
        $coordinates = new Coordinates(
            46.4273814334286,
            30.751279752912698,
        );

        $this->assertSame(46.42738, $coordinates->getLatitude());
        $this->assertSame(30.75128, $coordinates->getLongitude());
    }
}
