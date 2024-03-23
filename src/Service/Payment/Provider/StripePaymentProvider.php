<?php

namespace App\Service\Payment\Provider;

use App\Entity\User;
use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'prod')]
#[When(env: 'stage')]
class StripePaymentProvider implements PaymentProviderInterface
{
    public function __construct(
        private readonly StripeClient $stripeClient,
    ) {}

    public function createCustomer(User $user): string
    {
        $customer = $this->stripeClient->customers->create([
            'phone' => $user->getPhone(),
        ]);

        return $customer->id;
    }
}
