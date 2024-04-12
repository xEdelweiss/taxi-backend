<?php

namespace App\Tests\Unit\Service\Payment\Provider;

use App\Entity\User;
use App\Service\Payment\Provider\StripePaymentProvider;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Stripe\Service as StripeService;
use Stripe\StripeClient;
use Symfony\Component\Routing\RouterInterface;

class StripePaymentProviderTest extends Unit
{
    #[Test]
    public function createCustomer_returns_customer_id(): void
    {
        $user = new User(p(1));
        $stripeClient = $this->createMock(StripeClient::class);
        $stripeClient->customers = $this->createCustomersMockWith(expectedPhone: p(1), returnId: 'cus_123');
        $router = $this->createMock(RouterInterface::class);
        $paymentProvider = new StripePaymentProvider($stripeClient, $router, 'pk_test_123');

        $customerId = $paymentProvider->createCustomer($user);

        $this->assertSame('cus_123', $customerId);
    }

    private function createCustomersMockWith(string $expectedPhone, string $returnId): MockObject&StripeService\CustomerService
    {
        $mock = $this->createMock(StripeService\CustomerService::class);

        $mock
            ->expects($this->once())
            ->method('create')
            ->with(['phone' => $expectedPhone])
            ->willReturn((object)['id' => $returnId]);

        return $mock;
    }
}
