<?php

namespace App\Event;

class TripOrderPublished
{
    public function __construct(
        public int $orderId,
    ) {}
}
