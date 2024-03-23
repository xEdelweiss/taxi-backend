<?php

namespace App\Controller;

use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('/payment-method', methods: ['GET'], name: 'app_payment_method')]
    public function paymentForm(Request $request, StripeClient $stripe)
    {
        $key = $request->query->get('key');
        $keyData = json_decode(base64_decode($key), true, 512, JSON_THROW_ON_ERROR);
        $clientSecret = $keyData['client_secret'];
        $publishableKey = $keyData['publishable_key'];

        return $this->render('payment/payment-form.html.twig', [
            'clientSecret' => $clientSecret,
            'publicKey' => $publishableKey,
        ]);
    }

    #[Route('/payment-method/success', name: 'app_payment_method_success', methods: ['GET'])]
    public function paymentSuccess(Request $request, StripeClient $stripe)
    {
        try {
            $sessionId = $request->query->get('session_id');
            $session = $stripe->checkout->sessions->retrieve($sessionId);
            $userId = $session->metadata->user_id;
            $paymentIntent = $stripe->paymentIntents->retrieve($session->payment_intent);
            dump($sessionId, $session, $userId, $paymentIntent);
        } catch (\Throwable $e) {
            dump($e);
        }

        dd('SUCCESS');
    }

    #[Route('/payment/cancel', name: 'payment_cancel', methods: ['GET'])]
    public function paymentCancel(Request $request)
    {
        dump($request->query->all());
        dd('CANCEL');
    }


    #[Route('/holds', methods: ['GET'], name: 'app_payment_holds')]
    public function holdsForm(Request $request, StripeClient $stripe)
    {
        $key = $request->query->get('key');
        $keyData = json_decode(base64_decode($key), true, 512, JSON_THROW_ON_ERROR);
        $clientSecret = $keyData['client_secret'];
        $publishableKey = $keyData['publishable_key'];

        return $this->render('payment/payment-form.html.twig', [
            'clientSecret' => $clientSecret,
            'publicKey' => $publishableKey,
        ]);
    }

    #[Route('/holds/success', name: 'app_payment_hold_return', methods: ['GET'])]
    public function holdSuccess(Request $request)
    {
        dump($request->query->all());
        dd('HOLD SUCCESS');
    }
}
