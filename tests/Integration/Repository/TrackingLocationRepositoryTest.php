<?php

namespace App\Tests\Integration\Repository;

use App\Document\TrackingLocation;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TrackingLocationRepositoryTest extends KernelTestCase
{
    #[Test]
    public function findClosestDriversWorks(): void
    {
        $kernel = self::bootKernel();
        $documentManager = $kernel->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $documentManager->createQueryBuilder(TrackingLocation::class)
            ->remove()
            ->getQuery()
            ->execute();

        $location1 = new TrackingLocation(1, 46.43045, 30.75475, 'driver'); // 700m
        $location2 = new TrackingLocation(2, 46.46266, 30.74391, 'driver'); // 4.4km
        $location3 = new TrackingLocation(3, 46.42804, 30.74694, 'driver'); // 450m
        $documentManager->persist($location1);
        $documentManager->persist($location2);
        $documentManager->persist($location3);
        $documentManager->flush();

        $repository = $documentManager
            ->getRepository(TrackingLocation::class);

        $drivers = $repository->findClosestDrivers(46.42738, 30.75128, 400);
        $this->assertCount(1, $drivers);
        $this->assertSame(3, $drivers[0]->getUserId());

        $drivers = $repository->findClosestDrivers(46.42738, 30.75128, 1000);
        $this->assertCount(2, $drivers);

        $drivers = $repository->findClosestDrivers(46.42738, 30.75128, 5000);
        $this->assertCount(3, $drivers);
    }
}
