<?php

namespace App\Dto\Payment;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class CreatePaymentMethodPayload
{
    public function __construct(
        #[SerializedName('return_url')]
        public string $returnUrl,
    ) {}
}
