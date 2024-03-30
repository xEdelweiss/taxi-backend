<?php

namespace App\Entity;

use App\Dto\MoneyDto;
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

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $cost = null;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private ?string $currency = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $paymentHoldId = null;

    public function __construct()
    {
        $this->status = TripStatus::Initial;
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

    public function setCost(MoneyDto $money): static
    {
        $this->cost = $money->amount;
        $this->currency = $money->currency;

        return $this;
    }

    public function getCost(): ?MoneyDto
    {
        if ($this->cost === null) {
            return null;
        }

        return new MoneyDto($this->cost, $this->currency);
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

        if ($next === TripStatus::WaitingForPayment && !$this->getCost()) {
            throw new \LogicException('Cannot set status to WaitingForPayment without cost estimation');
        }

        if ($next === TripStatus::WaitingForDriver && !$this->getPaymentHoldId()) {
            throw new \LogicException('Cannot set status to WaitingForDriver without active payment hold');
        }
    }
}
