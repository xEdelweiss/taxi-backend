<?php

namespace App\Dto\Payment;

use App\Dto\AbstractResponse;
use App\Entity\TripOrder;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class HoldPaymentResponse extends AbstractResponse
{
    public string $id;

    #[SerializedName('order_id')]
    public int $orderId;

    public float $amount;
    public string $currency;
    public bool $captured;

    public function __construct(PaymentHoldDto $paymentHold, TripOrder $order, bool $captured = false)
    {
        $this->id = $paymentHold->id;
        $this->orderId = $order->getId();
        $this->amount = $order->getCost()->getAmount();
        $this->currency = $order->getCost()->getCurrency();
        $this->captured = $captured;
    }
}
