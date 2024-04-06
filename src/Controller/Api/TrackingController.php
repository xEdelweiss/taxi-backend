<?php

namespace App\Controller\Api;

use App\Attribute\Output;
use App\Document\TrackingLocation;
use App\Dto\Tracking\TrackLocationPayload;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Tracking')]
#[Route('/api/tracking')]
class TrackingController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $documentManager
    ) {}

    #[Route('/locations', methods: ['POST'])]
    #[Output(null, Response::HTTP_NO_CONTENT)]
    public function trackLocation(#[MapRequestPayload] TrackLocationPayload $payload): Response
    {
        $location = $this->documentManager->getRepository(TrackingLocation::class)
            ->findOneBy(['userId' => $this->getUser()->getId()]);

        $location ??= new TrackingLocation(
            $this->getUser()->getId(),
            $payload->latitude,
            $payload->longitude,
            $this->getUser()->isDriver() ? 'driver' : 'user',
        );

        $location->setCoordinates(
            $payload->latitude,
            $payload->longitude
        );

        $this->documentManager->persist($location);
        $this->documentManager->flush();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
