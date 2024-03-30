<?php

namespace App\Tests\Unit\Entity\Embeddable;

use App\Entity\Embeddable\Location;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase
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
