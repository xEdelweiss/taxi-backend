<?php

namespace App\Dto\Trip;

use App\Document\TrackingLocation;
use App\Dto\AbstractResponse;
use App\Dto\CarDetailsDto;
use App\Dto\CoordinatesDto;
use App\Dto\LocationDto;
use App\Dto\MinimalRouteDto;
use App\Dto\MoneyDto;
use App\Dto\RouteDto;
use App\Dto\UserInfoDto;
use App\Entity\TripOrder;
use App\Service\Trip\Enum\TripStatus;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class TripOrderResponse extends AbstractResponse
{
    public int $id;
    public TripStatus $status;
    public LocationDto $start;
    public LocationDto $finish;

    public MinimalRouteDto $route;

    public ?UserInfoDto $client;
    public ?UserInfoDto $driver;

    #[SerializedName('driver_location')]
    public ?CoordinatesDto $driverLocation;

    public ?CarDetailsDto $car;

    public MoneyDto $cost;

    #[SerializedName('start_eta')]
    public ?int $startEta;

    #[SerializedName('finish_eta')]
    public ?int $finishEta;

    public function __construct(
        TripOrder         $order,
        RouteDto          $routeDto,
        ?int              $startEta,
        ?TrackingLocation $driverTrackingLocation = null
    )
    {
        $this->id = $order->getId();
        $this->status = $order->getStatus();
        $this->start = LocationDto::fromEmbeddable($order->getStart());
        $this->finish = LocationDto::fromEmbeddable($order->getEnd());

        $this->route = MinimalRouteDto::fromRouteDto($routeDto);

        $this->client = UserInfoDto::fromUser($order->getUser());

        if ($order->getTripOrderRequest()) {
            $this->driver = UserInfoDto::fromUser($order->getTripOrderRequest()->getDriver()->getUser());
        } else {
            $this->driver = null;
        }

        $this->driverLocation = $driverTrackingLocation ? CoordinatesDto::fromTrackingLocation($driverTrackingLocation) : null;
        $this->car = null;

        $this->cost = MoneyDto::fromEmbeddable($order->getCost());

        if ($order->getStatus()->isActive()) {
            $this->startEta = $startEta;
            $this->finishEta = $startEta + $routeDto->duration;
        } else {
            $this->startEta = null;
            $this->finishEta = null;
        }
    }
}
