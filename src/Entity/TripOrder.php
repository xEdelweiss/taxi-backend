<?php

namespace App\Entity;

use App\Entity\Embeddable\Location;
use App\Entity\Embeddable\Money;
use App\Repository\TripOrderRepository;
use App\Service\Trip\Enum\TripStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TripOrderRepository::class)]
class TripOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private TripStatus $status;

    #[ORM\Embedded(class: Money::class)]
    private Money $cost;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $paymentHoldId = null;

    #[ORM\Embedded(class: Location::class)]
    private Location $start;

    #[ORM\Embedded(class: Location::class)]
    private Location $end;

    public function __construct()
    {
        $this->status = TripStatus::Initial;
        $this->cost = Money::empty();
        $this->start = Location::empty();
        $this->end = Location::empty();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): TripStatus
    {
        return $this->status;
    }

    public function setStatus(TripStatus $status): static
    {
        $this->ensureStatusChangeValid($this->status, $status);

        $this->status = $status;

        return $this;
    }

    public function setCost(Money $money): static
    {
        $this->cost = $money;

        return $this;
    }

    public function getCost(): Money
    {
        return $this->cost;
    }

    public function getStart(): ?Location
    {
        return $this->start;
    }

    public function setStart(Location $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): ?Location
    {
        return $this->end;
    }

    public function setEnd(Location $end): void
    {
        $this->end = $end;
    }

    public function getPaymentHoldId(): ?string
    {
        return $this->paymentHoldId;
    }

    public function setPaymentHoldId(?string $paymentHoldId): void
    {
        $this->paymentHoldId = $paymentHoldId;
    }

    private function ensureStatusChangeValid(TripStatus $previous, TripStatus $next): void
    {
        $order = [
            TripStatus::Initial,
            TripStatus::WaitingForPayment,
            TripStatus::WaitingForDriver,
            TripStatus::DriverOnWay,
            TripStatus::DriverArrived,
            TripStatus::InProgress,
            TripStatus::Completed,
        ];

        $previousIndex = array_search($previous, $order, true);
        $expectedNext = $order[$previousIndex + 1] ?? null;

        if ($expectedNext === null) {
            throw new \InvalidArgumentException("Invalid TripOrder status change: no expected steps after [{$previous->value}]");
        }

        if (in_array($next, [TripStatus::CanceledByDriver, TripStatus::CanceledByUser], true)) {
            if ($this->getPaymentHoldId()) {
                throw new \InvalidArgumentException('Cannot cancel order with active payment hold');
            }

            return;
        }

        if ($expectedNext !== $next) {
            throw new \InvalidArgumentException("Invalid TripOrder status change: expected [{$expectedNext->value}], got [{$next->value}]");
        }

        if (
            $next === TripStatus::WaitingForPayment
            && (
                $this->getCost()->isEmpty()
                || $this->getStart()->isEmpty()
                || $this->getEnd()->isEmpty()
            )
        ) {
            throw new \LogicException('Cannot set status to WaitingForPayment without cost estimation');
        }

        if ($next === TripStatus::WaitingForDriver && !$this->getPaymentHoldId()) {
            throw new \LogicException('Cannot set status to WaitingForDriver without active payment hold');
        }
    }
}
