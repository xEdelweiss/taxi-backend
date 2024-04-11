<?php

namespace App\Dto\Trip;

use App\Dto\AbstractResponse;
use App\Entity\TripOrder;

readonly class TripOrdersResponse extends AbstractResponse
{
    /** @var array<TripOrdersItemResponse> */
    public array $items;

    /** @param array<TripOrder> $orders */
    public function __construct(iterable $orders)
    {
        $this->items = array_map(
            static fn(TripOrder $order) => new TripOrdersItemResponse($order),
            $orders
        );
    }
}
