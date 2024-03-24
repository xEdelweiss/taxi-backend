<?php

namespace App\Controller\Api;

use App\Entity\TripOrder;
use App\Service\Payment\Dto\PaymentHoldDto;
use App\Service\Payment\ValueResolver\PaymentHoldValueResolver;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentService         $paymentService,
    ) {}

    #[Route('/payment-methods', methods: ['POST'])]
    public function addPaymentMethod(Request $request): JsonResponse
    {
        $returnUrl = $request->getPayload()->get('return_url');
        $url = $this->paymentService->getAddPaymentLink($this->getUser(), $returnUrl);

        return $this->json([
            'url' => $url,
        ], Response::HTTP_CREATED);
    }

    #[Route('/holds', methods: ['POST'])]
    public function hold(Request $request): JsonResponse
    {
        $payload = (object) $request->getPayload()->all();
        $order = $this->entityManager->getRepository(TripOrder::class)
            ->find($payload->order_id);

        if (!$order) {
            $this->createNotFoundException();
        }

        $hold = $this->paymentService->holdPaymentForOrder($this->getUser(), $order);

        return $this->json([
            'data' => [
                'id' => $hold->id,
                'order_id' => $order->getId(),
                'amount' => $order->getCost()->amount,
                'currency' => $order->getCost()->currency,
                'captured' => false,
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/holds/{hold}', methods: ['PUT'])]
    public function capture(
        Request $request,
        #[MapQueryString(resolver: PaymentHoldValueResolver::class)] PaymentHoldDto $hold
    ): Response {
        if ($request->getPayload()->get('captured') !== true) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $orderId = $this->paymentService->getOrderByPaymentHold($hold);

        $order = $this->entityManager->getRepository(TripOrder::class)
            ->find($orderId);

        if (!$order) {
            $this->createNotFoundException();
        }

        // @fixme prevent double capture
        $this->paymentService->capturePaymentHold($hold);

        return $this->json([
            'data' => [
                'id' => $hold->id,
                'order_id' => $order->getId(),
                'amount' => $order->getCost()->amount,
                'currency' => $order->getCost()->currency,
                'captured' => true,
            ],
        ]);
    }
}
