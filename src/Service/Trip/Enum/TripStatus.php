<?php

namespace App\Service\Trip\Enum;

enum TripStatus: string
{
    case Initial = 'INITIAL';
    case WaitingForPayment = 'WAITING_FOR_PAYMENT';
    case WaitingForDriver = 'WAITING_FOR_DRIVER';
    case DriverOnWay = 'DRIVER_ON_WAY';
    case DriverArrived = 'DRIVER_ARRIVED';
    case InProgress = 'IN_PROGRESS';
    case Completed = 'COMPLETED';
    case CanceledByUser = 'CANCELED_BY_USER';
    case CanceledByDriver = 'CANCELED_BY_DRIVER';

    /** @return TripStatus[] */
    public static function all(): array
    {
        return [
            self::Initial,
            self::WaitingForPayment,
            self::WaitingForDriver,
            self::DriverOnWay,
            self::DriverArrived,
            self::InProgress,
            self::Completed,
            self::CanceledByUser,
            self::CanceledByDriver,
        ];
    }

    public function isActive(): bool
    {
        return match ($this) {
            self::Completed, self::CanceledByUser, self::CanceledByDriver => false,
            default => true,
        };
    }

    public function isCanceled(): bool
    {
        return $this === self::CanceledByUser
            || $this === self::CanceledByDriver;
    }

    public function isBefore(TripStatus $status, bool $orEqual = false): bool
    {
        if ($orEqual) {
            return $this->ordinal() <= $status->ordinal();
        }

        return $this->ordinal() < $status->ordinal();
    }

    public function isAfter(TripStatus $status, bool $orEqual = false): bool
    {
        if ($orEqual) {
            return $this->ordinal() >= $status->ordinal();
        }

        return $this->ordinal() > $status->ordinal();
    }

    public function getNext(): self
    {
        $all = self::all();
        $next = $all[$this->ordinal() + 1];

        if ($next === null) {
            throw new \LogicException('No next status after ' . $this->value);
        }

        return $next;
    }

    private function ordinal(): int
    {
        return match ($this) {
            self::Initial => 0,
            self::WaitingForPayment => 1,
            self::WaitingForDriver => 2,
            self::DriverOnWay => 3,
            self::DriverArrived => 4,
            self::InProgress => 5,
            self::Completed => 6,
            self::CanceledByUser,
            self::CanceledByDriver => throw new \LogicException('Cannot compare canceled statuses'),
        };
    }
}
