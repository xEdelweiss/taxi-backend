<?php

namespace App\Tests\Integration\Service\Matching;

use App\Document\TrackingLocation;
use App\Entity\DriverProfile;
use App\Entity\Embeddable\Location;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Matching\SimpleFastestRouteMatchingStrategy;
use App\Service\NavigationService;
use App\Tests\Support\IntegrationTester;
use Codeception\Test\Unit;
use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\Attributes\Test;

class SimpleFastestRouteMatchingStrategyTest extends Unit
{
    protected IntegrationTester $tester;

    private DocumentManager $documentManager;

    protected function _before(): void
    {
        $this->documentManager = $this->tester->grabService('doctrine_mongodb.odm.default_document_manager');
        $this->documentManager->createQueryBuilder(TrackingLocation::class)
            ->remove()
            ->getQuery()
            ->execute();
    }

    #[Test]
    public function findMatchingDriver(): void
    {
        $location1 = new TrackingLocation(1, 46.43045, 30.75475, 'driver'); // 700m
        $location2 = new TrackingLocation(2, 46.46266, 30.74391, 'driver'); // 4.4km
        $location3 = new TrackingLocation(3, 46.42804, 30.74694, 'driver'); // 450m
        $location4 = new TrackingLocation(4, 46.42221, 30.75371, 'driver'); // 600m, but fastest route

        $this->documentManager->persist($location1);
        $this->documentManager->persist($location2);
        $this->documentManager->persist($location3);
        $this->documentManager->persist($location4);
        $this->documentManager->flush();

        $strategy = new SimpleFastestRouteMatchingStrategy(
            $this->documentManager->getRepository(TrackingLocation::class),
            $this->getUserRepositoryMock(),
            $this->tester->grabService(NavigationService::class),
        );

        $driverProfile = $strategy->findMatchingDriver(new Location('Some Address', 46.42738, 30.75128));
        $this->assertInstanceOf(DriverProfile::class, $driverProfile);
        $this->assertSame(4, $driverProfile->getUser()->getId());
    }

    private function getUserRepositoryMock(): UserRepository
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('find')
            ->willReturnCallback(fn($id) => $this->makeUser($id));

        return $userRepository;
    }

    private function makeUser(int $id): User
    {
        $user = new User(p($id));
        $user->setDriverProfile(new DriverProfile($user));

        $reflectionClass = new \ReflectionClass(User::class);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setValue($user, $id);

        return $user;
    }
}
