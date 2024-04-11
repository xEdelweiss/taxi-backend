<?php

namespace App\Dto\Trip;

use App\Dto\AbstractResponse;
use App\Dto\CarDetailsDto;
use App\Dto\LocationDto;
use App\Dto\MoneyDto;
use App\Dto\UserInfoDto;
use App\Entity\TripOrder;
use App\Service\Trip\Enum\TripStatus;

readonly class TripOrdersItemResponse extends AbstractResponse
{
    public int $id;
    public TripStatus $status;
    public LocationDto $start;
    public LocationDto $finish;

    // public null $route;

    public ?UserInfoDto $client;
    public ?UserInfoDto $driver;

    // #[SerializedName('driver_location')]
    // public null $driverLocation;

    public ?CarDetailsDto $car;

    public MoneyDto $cost;

    // #[SerializedName('start_eta')]
    // public null $startEta;

    // #[SerializedName('finish_eta')]
    // public null $finishEta;

    public function __construct(TripOrder $order)
    {
        $this->id = $order->getId();
        $this->status = $order->getStatus();
        $this->start = LocationDto::fromEmbeddable($order->getStart());
        $this->finish = LocationDto::fromEmbeddable($order->getEnd());

        // $this->route = null;

        $this->client = UserInfoDto::fromUser($order->getUser());

        if ($order->getTripOrderRequest()) {
            $this->driver = UserInfoDto::fromUser($order->getTripOrderRequest()->getDriver()->getUser());
        } else {
            $this->driver = null;
        }

        // $this->driverLocation = null;
        $this->car = null;

        $this->cost = MoneyDto::fromEmbeddable($order->getCost());

        // $this->startEta = null;
        // $this->finishEta = null;
    }
}
