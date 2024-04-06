<?php

namespace App\Dto\Trip;

use App\Dto\AbstractResponse;
use App\Dto\LocationDto;
use App\Entity\TripOrderRequest;
use App\Service\Trip\Enum\TripStatus;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class OrderRequestResponse extends AbstractResponse
{
    public int $id;
    public TripStatus $status;
    public LocationDto $start;
    public LocationDto $end;

    #[SerializedName('trip_time')]
    public float $tripTime;

    public function __construct(TripOrderRequest $orderRequest, float $tripTime)
    {
        if (!$orderRequest->getTripOrder()) {
            throw new \InvalidArgumentException('OrderRequestResponse must have a TripOrder');
        }

        $this->id = $orderRequest->getTripOrder()->getId(); // @fixme - should be $orderRequest->getId() + order_id
        $this->status = $orderRequest->getTripOrder()->getStatus();
        $this->start = LocationDto::fromEmbeddable($orderRequest->getTripOrder()->getStart());
        $this->end = LocationDto::fromEmbeddable($orderRequest->getTripOrder()->getEnd());
        $this->tripTime = $tripTime;
    }
}
