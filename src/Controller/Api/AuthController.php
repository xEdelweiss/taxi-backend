<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Event\UserRegistered;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    #[Route('/api/auth/register', methods: ['POST'], name: 'api_auth_register')]
    public function register(Request $request, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $payload = (object) $request->getPayload()->all();
        $user = new User($payload->phone);
        $user->setPassword($hasher->hashPassword($user, $payload->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserRegistered($user->getId()));

        return $this->json([
            'message' => 'Account created.',
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/auth/me', methods: ['GET'])]
    public function me(): Response
    {
        if (!$this->getUser()) {
            return $this->json(['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        $roles = array_filter(array_map(
            fn($role) => match ($role) {
                'ROLE_USER' => 'user',
                'ROLE_DRIVER' => 'driver',
                default => null,
            },
            $this->getUser()->getRoles()
        ));

        return $this->json([
            'id' => $this->getUser()->getId(),
            'phone' => $this->getUser()->getPhone(),
            'roles' => $roles,
        ]);
    }
}
