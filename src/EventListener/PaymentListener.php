<?php

namespace App\EventListener;

use App\Entity\User;
use App\Event\UserRegistered;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class PaymentListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentService $paymentService
    ) {}

    #[AsEventListener(event: UserRegistered::class)]
    public function onUserRegistered(UserRegistered $event): void
    {
        $user = $this->entityManager->find(User::class, $event->userId);
        $paymentCustomerId = $this->paymentService
            ->getPaymentProvider()
            ->createCustomer($user);

        // @fixme hardcoded property, will fail on provider change
        $user->setStripeCustomerId($paymentCustomerId);

        $this->entityManager->flush();
    }
}
