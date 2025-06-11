<?php

namespace App\Controller;

use App\Controller\Trait\ParseDtoTrait;
use App\Dto\RegisterDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RegisterController extends AbstractController
{
    use ParseDtoTrait;

    public function __construct(
        protected readonly SerializerInterface $serializer,
        protected readonly EntityManagerInterface $em,
        protected readonly DenormalizerInterface $denormalizer,
        protected readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload]
        RegisterDto $registerDto,
    ): JsonResponse {
        /** @var User */
        $user = $this->createWithDto($registerDto, User::class);

        $this->em->persist($user);
        $this->em->flush();

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $user->getPassword()),
        );

        $user->setRoles(['ROLE_USER']);

        $this->em->persist($user);
        $this->em->flush();

        $jsonData = $this->serializer->serialize($user, 'json', ['groups' => ['user']]);

        return new JsonResponse(
            $jsonData,
            Response::HTTP_CREATED,
            [],
            true,
        );
    }
}
