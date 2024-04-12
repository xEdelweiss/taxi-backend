<?php

namespace App\Service\Trip\Enum;

enum TripStatusFilter: string
{
    case All = 'ALL';
    case Active = 'ACTIVE';
    case Closed = 'CLOSED';
    case Completed = 'COMPLETED';
    case Canceled = 'CANCELED';

    /** @return TripStatus[] */
    public function toTripStatusList(): array
    {
        return match ($this) {
            self::All => TripStatus::all(),
            self::Active => [
                TripStatus::Initial,
                TripStatus::WaitingForPayment,
                TripStatus::WaitingForDriver,
                TripStatus::DriverOnWay,
                TripStatus::DriverArrived,
                TripStatus::InProgress,
            ],
            self::Closed => [TripStatus::Completed, TripStatus::CanceledByUser, TripStatus::CanceledByDriver],
            self::Completed => [TripStatus::Completed],
            self::Canceled => [TripStatus::CanceledByUser, TripStatus::CanceledByDriver],
        };
    }
}
