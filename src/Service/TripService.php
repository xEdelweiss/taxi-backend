<?php

namespace App\Service;

use App\Dto\LocationDto;
use App\Dto\RouteDto;
use App\Entity\DriverProfile;
use App\Entity\Embeddable\Location;
use App\Entity\Embeddable\Money;
use App\Entity\TripOrder;
use App\Entity\User;
use App\Repository\TrackingLocationRepository;
use App\Service\Trip\Enum\TripStatus;

readonly class TripService
{
    private const HARDCODED_ETA = 600;

    public function __construct(
        private NavigationService          $navigationService,
        private CostService                $costService,
        private TrackingLocationRepository $trackingLocationRepository
    ) {}

    public function createOrder(User $user, RouteDto $route): TripOrder
    {
        $cost = $this->costService->calculateCost($route);

        $order = new TripOrder($user);
        $order->setStart(new Location($route->start->address, $route->start->latitude, $route->start->longitude));
        $order->setEnd(new Location($route->finish->address, $route->finish->latitude, $route->finish->longitude));
        $order->setCost(new Money($cost, 'USD'));

        $order->setStatus(TripStatus::WaitingForPayment);

        return $order;
    }

    public function calculateEta(LocationDto $start, ?DriverProfile $driverProfile = null): int
    {
        if (!$driverProfile) {
            return $this->calculateInitialEta($start);
        }

        $trackingLocation = $this->trackingLocationRepository->findByUser($driverProfile->getUser());

        if (!$trackingLocation) {
            return $this->calculateInitialEta($start);
        }

        $route = $this->navigationService->calculateRoute(
            [
                $start->latitude,
                $start->longitude
            ],
            [
                $trackingLocation->getCoordinates()->getLatitude(),
                $trackingLocation->getCoordinates()->getLongitude()
            ]
        );

        return $route->duration;
    }

    private function calculateInitialEta(LocationDto $start): int
    {
        return static::HARDCODED_ETA;
    }

}
