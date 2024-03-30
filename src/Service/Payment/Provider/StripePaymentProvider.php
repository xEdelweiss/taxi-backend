<?php

namespace App\Service\Payment\Provider;

use App\Entity\TripOrder;
use App\Entity\User;
use App\Service\Payment\Dto\PaymentCredentialsDto;
use App\Service\Payment\Dto\PaymentHoldDto;
use Stripe\PaymentMethod;
use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

#[When(env: 'prod')]
#[When(env: 'stage')]
class StripePaymentProvider implements PaymentProviderInterface
{
    public function __construct(
        private readonly StripeClient    $stripeClient,
        private readonly RouterInterface $router,
        #[Autowire(env: 'STRIPE_PUBLISHABLE_KEY')]
        private readonly string          $stripePublishableKey,
    ) {}

    public function createCustomer(User $user): string
    {
        $customer = $this->stripeClient->customers->create([
            'phone' => $user->getPhone(),
        ]);

        return $customer->id;
    }

    public function getAddPaymentLink(User $user, string $returnUrl): string
    {
        $customerId = $user->getPaymentAccountId();

        // @fixme handle paymentAccountId is null

        $paymentProviderReturnUrl = $this->router
            ->generate(
                'app_payment_method_success',
                [
                    'return_url' => $returnUrl,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            ) . '&session_id={CHECKOUT_SESSION_ID}';

        $session = $this->stripeClient->checkout->sessions->create([
            'ui_mode' => 'embedded',
            'mode' => 'setup',
            'currency' => 'usd',
            'customer' => $customerId,
            'return_url' => $paymentProviderReturnUrl,
            'metadata' => [
                'user_id' => $user->getId(),
            ],
        ]);

        $credentials = new PaymentCredentialsDto(static::class, [
            'client_secret' => $session->client_secret,
            'publishable_key' => $this->stripePublishableKey,
        ]);

        return $this->router->generate(
            'app_payment_method',
            ['credentials' => (string)$credentials],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    public function holdPaymentForOrder(User $user, TripOrder $order): PaymentHoldDto
    {
        $customerId = $user->getPaymentAccountId();
        $defaultPaymentMethod = $this->getDefaultPaymentMethod($customerId);

        $paymentIntent = $this->stripeClient->paymentIntents->create([
            'amount' => $order->getCost()->getAmount(),
            'currency' => $order->getCost()->getCurrency(),
            'customer' => $customerId,
            'confirm' => true,
            'payment_method' => $defaultPaymentMethod->id,
            'capture_method' => 'manual',
            'metadata' => [
                'order_id' => $order->getId(),
            ],
            'return_url' => $this->router->generate('app_payment_hold_return', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
            // 'description' => 'Order #1', // An arbitrary string attached to the object. Often useful for displaying to users.
        ]);

        return new PaymentHoldDto($paymentIntent->id);
    }

    public function capturePaymentHold(PaymentHoldDto $hold): void
    {
        $paymentIntent = $this->stripeClient->paymentIntents->retrieve($hold->id);

        $paymentIntent->capture();
    }

    public function getOrderByPaymentHold(PaymentHoldDto $hold): int
    {
        $paymentIntent = $this->stripeClient->paymentIntents->retrieve($hold->id);

        return $paymentIntent->metadata->order_id;
    }

    private function getDefaultPaymentMethod(string $customerId): PaymentMethod
    {
        return $this->stripeClient->customers->allPaymentMethods($customerId, [
            'limit' => 1,
        ])->first();
    }
}
