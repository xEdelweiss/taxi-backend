<?php

namespace App\Dto\Trip;

use App\Dto\AbstractResponse;
use App\Entity\TripOrder;
use App\Service\Trip\Enum\TripStatus;

readonly class ShowOrderResponse extends AbstractResponse
{
    public int $id;
    public TripStatus $status;

    public function __construct(TripOrder $order)
    {
        $this->id = $order->getId();
        $this->status = $order->getStatus();
    }
}
