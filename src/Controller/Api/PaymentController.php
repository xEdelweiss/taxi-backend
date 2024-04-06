<?php

namespace App\Controller\Api;

use App\Attribute\Output;
use App\Dto\Payment\CapturePaymentPayload;
use App\Dto\Payment\CreatePaymentMethodPayload;
use App\Dto\Payment\CreatePaymentMethodResponse;
use App\Dto\Payment\HoldPaymentPayload;
use App\Dto\Payment\HoldPaymentResponse;
use App\Entity\TripOrder;
use App\Service\Payment\Dto\PaymentHoldDto;
use App\Service\Payment\ValueResolver\PaymentHoldValueResolver;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Payment')]
#[Route('/api/payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentService         $paymentService,
    ) {}

    #[Route('/payment-methods', methods: ['POST'])]
    #[Output(CreatePaymentMethodResponse::class, Response::HTTP_CREATED)]
    public function addPaymentMethod(#[MapRequestPayload] CreatePaymentMethodPayload $payload): JsonResponse
    {
        $url = $this->paymentService->getAddPaymentLink(
            $this->getUser(),
            $payload->returnUrl,
        );

        return $this->json(new CreatePaymentMethodResponse($url), Response::HTTP_CREATED);
    }

    #[Route('/holds', methods: ['POST'])]
    #[Output(HoldPaymentResponse::class, Response::HTTP_CREATED)]
    public function hold(#[MapRequestPayload] HoldPaymentPayload $payload): JsonResponse
    {
        $order = $this->entityManager->getRepository(TripOrder::class)
            ->find($payload->orderId);

        if (!$order) {
            $this->createNotFoundException();
        }

        $hold = $this->paymentService->holdPaymentForOrder($this->getUser(), $order);

        return $this->json(new HoldPaymentResponse($hold, $order, false), Response::HTTP_CREATED);
    }

    #[Route('/holds/{hold}', methods: ['PUT'])]
    #[Output(HoldPaymentResponse::class)]
    public function capture(
        #[MapRequestPayload] CapturePaymentPayload $payload,
        #[MapQueryString(resolver: PaymentHoldValueResolver::class)] PaymentHoldDto $hold
    ): Response {
        if ($payload->captured !== true) {
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

        return $this->json(new HoldPaymentResponse($hold, $order, true));
    }
}
