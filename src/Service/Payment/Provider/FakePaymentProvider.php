<?php

namespace App\Service\Payment\Provider;

use App\Dto\Payment\PaymentCredentialsDto;
use App\Dto\Payment\PaymentHoldDto;
use App\Entity\TripOrder;
use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class FakePaymentProvider implements PaymentProviderInterface
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {}

    public function createCustomer(User $user): string
    {
        return 'fake-cid-' . $user->getPhone();
    }

    public function getAddPaymentLink(User $user, string $returnUrl): string
    {
        $credentials = new PaymentCredentialsDto(static::class, [
            'return_url' => $this->router->generate(
                'app_payment_method_success',
                ['return_url' => $returnUrl],
                referenceType: UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return $this->router->generate(
            'app_payment_method',
            ['credentials' => (string)$credentials],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function holdPaymentForOrder(User $user, TripOrder $order): PaymentHoldDto
    {
        return new PaymentHoldDto("fake-uid-{$user->getId()}-oid-{$order->getId()}");
    }

    public function capturePaymentHold(PaymentHoldDto $hold): void
    {
        // do nothing
    }

    public function getOrderByPaymentHold(PaymentHoldDto $hold): int
    {
        preg_match('/oid-(?P<orderId>\d+)/', $hold->id, $matches);

        if (isset($matches['orderId'])) {
            return (int)$matches['orderId'];
        }

        throw new \InvalidArgumentException('Invalid payment hold id');
    }
}
