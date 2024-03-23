<?php

namespace App\Controller\Api;

use App\Entity\TripOrder;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/api/payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('/payment-methods', methods: ['POST'])]
    public function addPaymentMethod(StripeClient $stripe): JsonResponse
    {
        $stripeCustomerId = $this->getUser()->getStripeCustomerId();

        $session = $stripe->checkout->sessions->create([
            'ui_mode' => 'embedded',
            'mode' => 'setup',
            'currency' => 'usd',
            'customer' => $stripeCustomerId,
            'return_url' => $this->generateUrl('app_payment_method_success', [], UrlGeneratorInterface::ABSOLUTE_URL)
                . '?session_id={CHECKOUT_SESSION_ID}',
            'metadata' => [
                'user_id' => $this->getUser()->getId(),
            ],
        ]);

        $key = base64_encode(json_encode([
            'client_secret' => $session->client_secret,
            'publishable_key' => 'pk_test_51OxQvbRruITC6eW2u18sC5WORWssLKiLzd0vheT9Twi0KgKDsUNw1HnC0iEpzJq3FxLBkZZwHm2bPw1btsF5vycZ00tKQCBG9W',
        ], JSON_THROW_ON_ERROR));

        return $this->json([
            'url' => $this->generateUrl('app_payment_method', [
                'key' => $key,
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ], Response::HTTP_CREATED);
    }

    #[Route('/holds', methods: ['POST'])]
    public function hold(Request $request, StripeClient $stripe): JsonResponse
    {
        $payload = (object)$request->getPayload()->all();
        $order = $this->entityManager->getRepository(TripOrder::class)->find($payload->order_id);
        $customerId = $this->getUser()->getStripeCustomerId();

        if (!$order) {
            $this->createNotFoundException();
        }

        $paymentMethods = $stripe->customers->allPaymentMethods($customerId, [
            'limit' => 1,
        ]);

        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $order->getCost()->amount,
            'currency' => $order->getCost()->currency,
            'customer' => $customerId,
            'confirm' => true,
            'payment_method' => $paymentMethods->first()->id,
            'capture_method' => 'manual',
            'metadata' => [
                'order_id' => $payload->order_id,
            ],
            'return_url' => $this->generateUrl('app_payment_hold_return', [], UrlGeneratorInterface::ABSOLUTE_URL),
            // 'description' => 'Order #1', // An arbitrary string attached to the object. Often useful for displaying to users.
        ]);

        return $this->json([
            'data' => [
                'id' => $paymentIntent->id,
                'order_id' => $order->getId(),
                'amount' => $order->getCost()->amount,
                'currency' => $order->getCost()->currency,
                'captured' => false,
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/holds/{hold}', methods: ['PUT'])]
    public function capture(Request $request, string $hold, StripeClient $stripe): Response
    {
        if ($request->getPayload()->get('captured') !== true) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $paymentIntent = $stripe->paymentIntents->retrieve($hold);
        $order = $this->entityManager->getRepository(TripOrder::class)->find($paymentIntent->metadata->order_id);

        $paymentIntent->capture();

        return $this->json([
            'data' => [
                'id' => $paymentIntent->id,
                'order_id' => $order->getId(),
                'amount' => $order->getCost()->amount,
                'currency' => $order->getCost()->currency,
                'captured' => true,
            ],
        ]);
    }

    #[Route('/fake-provider', methods: ['GET'])]
    public function fakeProvider(#[Autowire('%kernel.environment%')] string $env, Request $request): Response
    {
        if ($env === 'production') {
            $this->createNotFoundException();
        }

        $orderId = $request->query->get('order_id');
        $order = $this->entityManager->getRepository(TripOrder::class)->find($orderId);
        $order->setStatus('WAITING_FOR_DRIVER');

        $this->entityManager->flush();

        return new Response(status: Response::HTTP_OK);
    }
}
