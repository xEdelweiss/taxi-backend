<?php

namespace App\Dto\Trip;

use App\Dto\AbstractResponse;
use App\Dto\LocationDto;
use App\Dto\MoneyDto;
use App\Dto\RouteDto;
use App\Entity\TripOrder;
use App\Service\Trip\Enum\TripStatus;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class UserOrderResponse extends AbstractResponse
{
    public int $id;
    public TripStatus $status;
    public LocationDto $start;
    public LocationDto $end;
    public MoneyDto $cost;

    #[SerializedName('trip_time')]
    public float $tripTime;

    #[SerializedName('user_id')]
    public int $userId;

    public function __construct(TripOrder $order, RouteDto $route)
    {
        $this->id = $order->getId();
        $this->status = $order->getStatus();
        $this->start = LocationDto::fromEmbeddable($order->getStart());
        $this->end = LocationDto::fromEmbeddable($order->getEnd());
        $this->cost = MoneyDto::fromEmbeddable($order->getCost());
        $this->tripTime = $route->duration;
        $this->userId = $order->getUser()->getId();
    }
}
