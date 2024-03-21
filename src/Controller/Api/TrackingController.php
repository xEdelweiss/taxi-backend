<?php

namespace App\Controller\Api;

use App\Document\TrackingLocation;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tracking')]
class TrackingController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $documentManager
    ) {}

    #[Route('/locations', methods: ['POST'])]
    public function trackLocation(Request $request): Response
    {
        $latitude = $request->getPayload()->get('latitude');
        $longitude = $request->getPayload()->get('longitude');

        $location = $this->documentManager->getRepository(TrackingLocation::class)
            ->findOneBy(['userId' => $this->getUser()->getId()]);

        $location ??= new TrackingLocation(
            $this->getUser()->getId(),
            $latitude,
            $longitude
        );

        $location->setCoordinates(
            $request->getPayload()->get('latitude'),
            $request->getPayload()->get('longitude')
        );

        $this->documentManager->persist($location);
        $this->documentManager->flush();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
