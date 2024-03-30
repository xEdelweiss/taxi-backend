<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Embeddable\Money;
use App\Entity\TripOrder;
use App\Service\Trip\Enum\TripStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class TripOrderStatusFlowTest extends TestCase
{
    #[Test]
    public function createdWithInitialStatus(): TripOrder
    {
        $order = new TripOrder();

        $this->assertSame(TripStatus::Initial, $order->getStatus());
        $this->assertNull($order->getPaymentHoldId());

        return $order;
    }

    #[Test]
    public function canChangeFromInitialToWaitingForPaymentIfHasCost(): void
    {
        $order = $this->makeTripOrder(TripStatus::Initial);

        $order->setCost(new Money(100, 'USD'));
        $order->setStatus(TripStatus::WaitingForPayment);

        $this->assertSame(TripStatus::WaitingForPayment, $order->getStatus());
    }

    #[Test]
    public function cannotChangeFromInitialToWaitingForPaymentIfNoCost(): void
    {
        $order = $this->makeTripOrder(TripStatus::Initial);

        $this->expectException(\LogicException::class);
        $order->setStatus(TripStatus::WaitingForPayment);
    }

    #[Test]
    public function canChangeFromWaitingForPaymentToWaitingForDriverIfPaid(): void
    {
        $order = $this->makeTripOrder(TripStatus::WaitingForPayment);

        $order->setPaymentHoldId('fake-payment-hold-id');
        $order->setStatus(TripStatus::WaitingForDriver);

        $this->assertSame(TripStatus::WaitingForDriver, $order->getStatus());
    }

    #[Test]
    public function cannotChangeFromWaitingForPaymentToWaitingForDriverIfNotPaid(): void
    {
        $order = $this->makeTripOrder(TripStatus::WaitingForPayment);

        $this->expectException(\LogicException::class);
        $order->setStatus(TripStatus::WaitingForDriver);
    }

    #[Test]
    public function canChangeFromWaitingForDriverToDriverOnWay(): void
    {
        $order = $this->makeTripOrder(TripStatus::WaitingForDriver);

        $order->setStatus(TripStatus::DriverOnWay);

        $this->assertSame(TripStatus::DriverOnWay, $order->getStatus());
    }

    #[Test]
    public function canChangeFromDriverOnWayToDriverArrived(): void
    {
        $order = $this->makeTripOrder(TripStatus::DriverOnWay);

        $order->setStatus(TripStatus::DriverArrived);

        $this->assertSame(TripStatus::DriverArrived, $order->getStatus());
    }

    #[Test]
    public function canChangeFromDriverArrivedToInProgress(): void
    {
        $order = $this->makeTripOrder(TripStatus::DriverArrived);

        $order->setStatus(TripStatus::InProgress);

        $this->assertSame(TripStatus::InProgress, $order->getStatus());
    }

    #[Test]
    public function canChangeFromInProgressToCompleted(): void
    {
        $order = $this->makeTripOrder(TripStatus::InProgress);

        $order->setStatus(TripStatus::Completed);

        $this->assertSame(TripStatus::Completed, $order->getStatus());
    }

    #[Test]
    #[TestWith([TripStatus::Initial])]
    #[TestWith([TripStatus::WaitingForPayment])]
    public function canCancelBeforePaid(TripStatus $status): void
    {
        $order = $this->makeTripOrder($status);

        $order->setStatus(TripStatus::CanceledByUser);

        $this->assertSame(TripStatus::CanceledByUser, $order->getStatus());
    }

    #[Test]
    #[TestWith([TripStatus::CanceledByUser])]
    #[TestWith([TripStatus::CanceledByDriver])]
    public function canCancelAfterPaidIfPaymentCanceled(TripStatus $status): void
    {
        $order = $this->makeTripOrder(TripStatus::WaitingForDriver);

        $order->setPaymentHoldId(null);
        $order->setStatus($status);

        $this->assertSame($status, $order->getStatus());
    }

    #[Test]
    #[TestWith([TripStatus::CanceledByUser])]
    #[TestWith([TripStatus::CanceledByDriver])]
    public function cannotCancelIfHasPaymentHoldId(TripStatus $status): void
    {
        $order = $this->makeTripOrder(TripStatus::WaitingForDriver);

        $this->expectException(\LogicException::class);
        $order->setStatus($status);
    }

    #[Test]
    #[TestWith([TripStatus::CanceledByUser])]
    #[TestWith([TripStatus::InProgress])]
    public function cannotChangeCompletedStatus(TripStatus $status): void
    {
        $order = $this->makeTripOrder(TripStatus::Completed);

        $this->expectException(\LogicException::class);
        $order->setStatus($status);
    }

    #[Test]
    #[TestWith([TripStatus::WaitingForDriver, TripStatus::Completed])]
    #[TestWith([TripStatus::DriverOnWay, TripStatus::WaitingForDriver])]
    public function cannotSkipStatusOrder(TripStatus $from, TripStatus $to): void
    {
        $order = $this->makeTripOrder($from);

        $this->expectException(\LogicException::class);
        $order->setStatus($to);
    }

    #[Test]
    #[TestWith([TripStatus::DriverOnWay, TripStatus::WaitingForDriver])]
    public function cannotRewindStatusOrder(TripStatus $from, TripStatus $to): void
    {
        $order = $this->makeTripOrder($from);

        $this->expectException(\LogicException::class);
        $order->setStatus($to);
    }

    private function makeTripOrder(TripStatus $status): TripOrder
    {
        $tripOrder = new TripOrder();

        $reflectionClass = new \ReflectionClass(TripOrder::class);
        $reflectionProperty = $reflectionClass->getProperty('status');
        $reflectionProperty->setValue($tripOrder, $status);

        if (in_array($status, [
            TripStatus::WaitingForPayment,
            TripStatus::WaitingForDriver,
            TripStatus::DriverOnWay,
            TripStatus::DriverArrived,
            TripStatus::InProgress,
            TripStatus::Completed,
        ], true)) {
            $reflectionProperty = $reflectionClass->getProperty('cost');
            $reflectionProperty->setValue($tripOrder, new Money(100, 'USD'));
        }

        if (in_array($status, [
            TripStatus::WaitingForDriver,
            TripStatus::DriverOnWay,
            TripStatus::DriverArrived,
            TripStatus::InProgress,
            TripStatus::Completed,
        ], true)) {
            $reflectionProperty = $reflectionClass->getProperty('paymentHoldId');
            $reflectionProperty->setValue($tripOrder, 'fake-payment-hold-id');
        }

        if (in_array($status, [TripStatus::CanceledByDriver, TripStatus::CanceledByUser], true)) {
            $reflectionProperty = $reflectionClass->getProperty('paymentHoldId');
            $reflectionProperty->setValue($tripOrder, null);
        }

        return $tripOrder;
    }
}
