<?php

namespace App\Dto\Trip;

use App\Dto\AbstractResponse;
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
                    [
                        $orderRequest->getTripOrder()->getStart()->getLatitude(),
                        $orderRequest->getTripOrder()->getStart()->getLongitude()
                    ],
                    [
                        $orderRequest->getTripOrder()->getEnd()->getLatitude(),
                        $orderRequest->getTripOrder()->getEnd()->getLongitude()
                    ]
                )->duration,
            ),
            $orderRequests
        );
    }
}
