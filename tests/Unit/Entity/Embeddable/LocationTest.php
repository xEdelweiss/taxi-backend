<?php

namespace App\Tests\Unit\Entity\Embeddable;

use App\Entity\Embeddable\Location;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\Test;

class LocationTest extends Unit
{
    #[Test]
    public function trimsCoordinates(): void
    {
        $location = new Location(
            '7th st. Fontanskoyi dorohy',
            46.4273814334286,
            30.751279752912698,
        );

        $this->assertSame(46.42738, $location->getLatitude());
        $this->assertSame(30.75128, $location->getLongitude());
    }
}
