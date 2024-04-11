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

    public function isActive(): bool
    {
        return match ($this) {
            self::Completed, self::CanceledByUser, self::CanceledByDriver => false,
            default => true,
        };
    }
}
