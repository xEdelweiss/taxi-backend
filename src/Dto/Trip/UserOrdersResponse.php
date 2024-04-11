<?php

namespace App\Dto\Trip;

use App\Dto\AbstractResponse;
use App\Dto\LocationDto;
use App\Entity\TripOrder;
use App\Service\NavigationService;

readonly class UserOrdersResponse extends AbstractResponse
{
    /** @var array<UserOrderResponse()> */
    public array $items;

    /** @param array<TripOrder> $orders */
    public function __construct(iterable $orders, NavigationService $navigationService)
    {
        $this->items = array_map(
            static fn (TripOrder $order) => new UserOrderResponse(
                $order,
                $navigationService->calculateRoute(
                    LocationDto::fromEmbeddable($order->getStart()),
                    LocationDto::fromEmbeddable($order->getEnd()),
                ),
            ),
            $orders
        );
    }
}
