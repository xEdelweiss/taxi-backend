<?php

namespace App\Dto\Payment;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class HoldPaymentPayload
{
    public function __construct(
        #[SerializedName('order_id')]
        public int $orderId,
    ) {}
}
