<?php

namespace App\Controller\Api;

use App\Attribute\Output;
use App\Dto\Auth\CurrentUserResponse;
use App\Dto\Auth\RegisterPayload;
use App\Dto\Auth\RegisterResponse;
use App\Entity\User;
use App\Event\UserRegistered;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Authentication')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    #[Route('/api/auth/register', methods: ['POST'], name: 'api_auth_register')]
    #[Output(RegisterResponse::class, Response::HTTP_CREATED)]
    public function register(#[MapRequestPayload] RegisterPayload $payload, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $user = new User($payload->phone);
        $user->setPassword($hasher->hashPassword($user, $payload->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserRegistered($user->getId()));

        return $this->json(new RegisterResponse(), Response::HTTP_CREATED);
    }

    #[Route('/api/auth/me', methods: ['GET'])]
    #[Output(CurrentUserResponse::class)]
    public function me(): Response
    {
        if (!$this->getUser()) {
            return $this->json(['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json(new CurrentUserResponse($this->getUser()));
    }
}
