<?php

namespace App\Controller;

use App\Document\TrackingLocation;
use App\Entity\DriverProfile;
use App\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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

    #[Route('/debug/users', methods: ['POST'])]
    public function addUser(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $type = $request->getPayload()->get('type');
        $dbConnection = $this->entityManager->getConnection();
        $nextValQuery = $dbConnection->getDatabasePlatform()->getSequenceNextValSQL('user_id_seq');
        $lastId = (int) $dbConnection->executeQuery($nextValQuery)->fetchOne();

        $nextPhone = p($lastId + 1, $type === 'user' ? '380110000000' : '380220000000');

        $user = new User($nextPhone);
        $user->setPassword($passwordHasher->hashPassword($user, '!password!'));
        $this->entityManager->persist($user);

        if ($type === 'driver') {
            $user->setDriverProfile(new DriverProfile($user));
        }

        $this->entityManager->flush();

        return $this->json([
            'type' => $user->getDriverProfile() ? 'driver' : 'user',
            'phone' => $user->getPhone(),
            'status' => 'No order',
        ]);
    }

    #[Route(path: '/debug/users', methods: ['DELETE'])]
    public function removeUser(Request $request): Response
    {
        $phone = $request->getPayload()->get('phone');
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['phone' => $phone]);

        // todo do not remove user with active order

        if (!$user) {
            return $this->json([
                'error' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $location = $this->documentManager
            ->getRepository(TrackingLocation::class)
            ->findByUser($user);

        if ($location) {
            $this->documentManager->remove($location);
            $this->documentManager->flush();
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json([
            'phone' => $phone,
        ], Response::HTTP_OK);
    }

    #[Route('/debug/users', methods: ['GET'])]
    public function getUsers(): Response
    {
        $users = $this->entityManager->getRepository(User::class)
            ->findAll();

        // list of drivers with coordinates by order
        $locations = $this->documentManager
            ->getRepository(TrackingLocation::class)
            ->findAll();

        return $this->json([
            'items' => array_map(fn(User $user) => [
                'type' => $user->getDriverProfile() ? 'driver' : 'user',
                'phone' => $user->getPhone(),
                'coordinates' => $this->findCoordinates($locations, $user),
                'status' => 'No order',
            ], $users),
        ]);
    }

    #[Route(path: '/debug/last-location')]
    public function getLastLocation(Request $request): Response
    {
        $phones = $request->get('phones', []);

        $users = $this->entityManager->getRepository(User::class)
            ->findBy(['phone' => $phones]);

        $usersIds = array_map(fn(User $user) => $user->getId(), $users);

        $locations = $this->documentManager
            ->getRepository(TrackingLocation::class)
            ->findBy(['userId' => ['$in' => $usersIds]]);

        return $this->json([
            'items' => array_map(fn(TrackingLocation $location) => [
                'phone' => $this->entityManager->find(User::class, $location->getUserId())->getPhone(),
                'coordinates' => [
                    'latitude' => $location->getCoordinates()->getLatitude(),
                    'longitude' => $location->getCoordinates()->getLongitude(),
                ],
            ], $locations),
        ]);
    }

    /** @param TrackingLocation[] $locations */
    private function findCoordinates(array $locations, User $user): ?array
    {
        foreach ($locations as $location) {
            if ($location->getUserId() === $user->getId()) {
                return [
                    'latitude' => $location->getCoordinates()->getLatitude(),
                    'longitude' => $location->getCoordinates()->getLongitude(),
                ];
            }
        }

        return null;
    }
}
