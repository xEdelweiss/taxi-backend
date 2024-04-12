<?php

namespace App\Tests\Integration\Repository;

use App\Entity\TripOrder;
use App\Service\Trip\Enum\TripStatus;
use App\Service\Trip\Enum\TripStatusFilter;
use App\Tests\Support\IntegrationTester;
use Codeception\Attribute\Examples;
use Codeception\Test\Unit;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;

class TripOrderRepositoryTest extends Unit
{
    protected IntegrationTester $tester;

    private ?EntityManagerInterface $entityManager;

    protected function _before()
    {
        $this->entityManager = $this->tester->grabService(EntityManagerInterface::class);
    }

    #[Test]
    public function findActiveOrders(): void
    {
        $user = $this->tester->haveUser(p(1));
        $this->tester->haveTripOrder($user, TripStatus::Initial);
        $this->tester->haveTripOrder($user, TripStatus::InProgress);
        $this->tester->haveTripOrder($user, TripStatus::WaitingForDriver);
        $this->tester->haveTripOrder($user, TripStatus::Completed);
        $this->tester->haveTripOrder($user, TripStatus::CanceledByUser);

        $repository = $this->entityManager->getRepository(TripOrder::class);

        $orders = $repository->findActiveOrders();
        $this->assertCount(3, $orders);
    }

    #[Test]
    #[Examples(null, 5)]
    #[Examples(TripStatusFilter::All, 5)]
    #[Examples(TripStatusFilter::Active, 3)]
    #[Examples(TripStatusFilter::Closed, 2)]
    #[Examples(TripStatusFilter::Completed, 1)]
    #[Examples(TripStatusFilter::Canceled, 1)]
    public function findUserOrders(?TripStatusFilter $tripStatusFilter, int $expectedCount): void
    {
        $user = $this->tester->haveUser(p(1));
        $this->tester->haveTripOrder($user, TripStatus::Initial);
        $this->tester->haveTripOrder($user, TripStatus::InProgress);
        $this->tester->haveTripOrder($user, TripStatus::WaitingForDriver);
        $this->tester->haveTripOrder($user, TripStatus::Completed);
        $this->tester->haveTripOrder($user, TripStatus::CanceledByUser);

        $repository = $this->entityManager->getRepository(TripOrder::class);

        $orders = $repository->findUserOrders($user, $tripStatusFilter?->toTripStatusList());
        $this->assertCount($expectedCount, $orders);
    }

    #[Test]
    #[Examples(null, 2)]
    #[Examples(TripStatusFilter::Active, 1)]
    #[Examples(TripStatusFilter::Completed, 1)]
    public function findUserOrdersIncludingRequests_returnsUserOrders(?TripStatusFilter $statusFilter, int $expectedCount): void
    {
        // given
        $user = $this->tester->haveUser(p(1));
        $driver = $this->tester->haveDriver(p(2));

        $this->tester->haveTripOrderWithDriver($user, TripStatus::WaitingForDriver, $driver);
        $this->tester->haveTripOrder($user, TripStatus::Completed);

        $repository = $this->entityManager->getRepository(TripOrder::class);

        // when
        $orders = $repository->findUserOrdersIncludingRequests($user, $statusFilter?->toTripStatusList());

        // then
        $this->assertCount($expectedCount, $orders);
    }

    #[Test]
    #[Examples(null, 1)]
    #[Examples(TripStatusFilter::Active, 1)]
    #[Examples(TripStatusFilter::Completed, 0)]
    public function findUserOrdersIncludingRequests_includesOrderRequestsForDriver(?TripStatusFilter $statusFilter, int $expectedCount): void
    {
        // given
        $user = $this->tester->haveUser(p(1));
        $driver = $this->tester->haveDriver(p(2));

        $this->tester->haveTripOrderWithDriver($user, TripStatus::WaitingForDriver, $driver);
        $this->tester->haveTripOrder($user, TripStatus::Completed);

        $repository = $this->entityManager->getRepository(TripOrder::class);

        // when
        $orders = $repository->findUserOrdersIncludingRequests($driver, $statusFilter?->toTripStatusList());

        // then
        $this->assertCount($expectedCount, $orders);
    }
}
