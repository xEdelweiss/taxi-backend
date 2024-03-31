<?php

namespace App\Controller;

use App\Document\TrackingLocation;
use App\Entity\DriverProfile;
use App\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DebugUiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DocumentManager $documentManager,
    ) {}

    #[Route('/')]
    public function index(): Response
    {
        return $this->render('debug_ui/index.html.twig');
    }

    #[Route('/debug/add-user')]
    public function addUser(Request $request): Response
    {
        $type = $request->getPayload()->get('type');
        $dbConnection = $this->entityManager->getConnection();
        $nextValQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL('user_id_seq');
        $lastId = (int) $dbConnection->executeQuery($nextValQuery)->fetchOne();

        $nextPhone = p($lastId + 1, $type === 'user' ? '380110000000' : '380220000000');

        $user = new User($nextPhone);
        $user->setPassword('!password!');
        $this->entityManager->persist($user);

        if ($type === 'driver') {
            $user->setDriverProfile(new DriverProfile($user));
        }

        $this->entityManager->flush();

        return $this->json([
            'data' => [
                'id' => $user->getId(),
                'phone' => $user->getPhone(),
                'driver_profile_id' => $user->getDriverProfile()?->getId(),
            ],
        ]);
    }

    #[Route(path: '/debug/remove-user')]
    public function removeUser(Request $request): Response
    {
        $phone = $request->getPayload()->get('phone');
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['phone' => $phone]);

        // todo do not remove user with active order

        if ($user) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        return $this->json([
            'data' => [
                'phone' => $phone,
            ],
        ], $user ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    #[Route('/debug/get-users')]
    public function getUsers(): Response
    {
        $drivers = $this->entityManager->getRepository(User::class)->findAllWithDriverProfile();
        $users = $this->entityManager->getRepository(User::class)->findAllWithoutDriverProfile();

        return $this->json([
            'data' => [
                'drivers' => array_map(fn(User $user) => ['phone' => $user->getPhone()], $drivers),
                'users' => array_map(fn(User $user) => ['phone' => $user->getPhone()], $users),
            ],
        ]);
    }

    #[Route(path: '/debug/fake-login')]
    public function fakeLogin(Request $request, JWTTokenManagerInterface $JWTManager): Response
    {
        $phone = $request->getPayload()->get('phone');
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['phone' => $phone]);

        if (!$user) {
            return $this->json([
                'error' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $trackingLocation = $this->documentManager
            ->getRepository(TrackingLocation::class)
            ->findByUser($user);

        return $this->json([
            'data' => [
                'token' => $JWTManager->create($user),
                'coordinates' => $trackingLocation ? [
                    'latitude' => $trackingLocation->getCoordinates()->getLatitude(),
                    'longitude' => $trackingLocation->getCoordinates()->getLongitude(),
                ] : null,
            ],
        ]);
    }
}
