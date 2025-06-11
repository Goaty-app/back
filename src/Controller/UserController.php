<?php

namespace App\Controller;

use App\Controller\Trait\ParseDtoTrait;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api', name: 'api_user_')]
class UserController extends AbstractController
{
    use ParseDtoTrait;

    public function __construct(
        protected readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/v1/me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User */
        $user = $this->getUser();

        $jsonData = $this->serializer->serialize($user, 'json', ['groups' => ['me']]);

        return new JsonResponse(
            $jsonData,
            Response::HTTP_OK,
            [],
            true,
        );
    }
}
