<?php

namespace App\Controller;

use App\Service\Payment\Dto\PaymentCredentialsDto;
use App\Service\Payment\Provider\FakePaymentProvider;
use App\Service\Payment\Provider\StripePaymentProvider;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('/payment-method', methods: ['GET'], name: 'app_payment_method')]
    public function paymentForm(PaymentCredentialsDto $credentials)
    {
        if ($credentials->provider === StripePaymentProvider::class) {
            return $this->render('payment/add-payment-method/stripe.html.twig', [
                'clientSecret' => $credentials->get('client_secret'),
                'publicKey' => $credentials->get('publishable_key'),
            ]);
        }

        if ($credentials->provider === FakePaymentProvider::class) {
            return $this->render('payment/add-payment-method/fake.html.twig', [
                'paymentReturnUrl' => explode('?', $credentials->get('return_url'))[0],
                'appReturnUrl' => Request::create($credentials->get('return_url'))->query->get('return_url'),
            ]);
        }

        throw $this->createNotFoundException();
    }

    #[Route('/payment-method/success', name: 'app_payment_method_success', methods: ['GET'])]
    public function paymentSuccess(Request $request)
    {
        // $sessionId = $request->query->get('session_id');
        // $session = $stripe->checkout->sessions->retrieve($sessionId);
        // $userId = $session->metadata->user_id;
        // $paymentIntent = $stripe->paymentIntents->retrieve($session->payment_intent);

        return $this->redirect($request->get('return_url'));
    }

    #[Route('/payment/cancel', name: 'payment_cancel', methods: ['GET'])]
    public function paymentCancel()
    {
        throw new \RuntimeException('Not implemented');
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
    public function holdSuccess()
    {
        throw new \RuntimeException('Not implemented');
    }
}
