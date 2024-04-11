<?php

namespace App\Dto\Trip;

use App\Dto\AbstractResponse;
use App\Dto\LocationDto;
use App\Entity\TripOrderRequest;
use App\Service\NavigationService;

readonly class OrderRequestsResponse extends AbstractResponse
{
    /** @var array<OrderRequestResponse> */
    public array $items;

    /** @param array<TripOrderRequest> $orderRequests */
    public function __construct(array $orderRequests, NavigationService $navigationService)
    {
        $this->items = array_map(
            static fn (TripOrderRequest $orderRequest) => new OrderRequestResponse(
                $orderRequest,
                $navigationService->calculateRoute(
                    LocationDto::fromEmbeddable($orderRequest->getTripOrder()->getStart()),
                    LocationDto::fromEmbeddable($orderRequest->getTripOrder()->getEnd()),
                )->duration,
            ),
            $orderRequests
        );
    }
}
