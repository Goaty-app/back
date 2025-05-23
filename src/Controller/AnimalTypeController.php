<?php

namespace App\Controller;

use App\Entity\AnimalType;
use App\Repository\AnimalTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api', name: 'api_animal_type_')]
final class AnimalTypeController extends AbstractCachedController
{
    public static function getCacheKey(): string
    {
        return 'animalTypes';
    }

    public static function getGroupCacheKey(): string
    {
        return AnimalController::getGroupCacheKey();
    }

    #[Route('/v1/animal-type', name: 'get_all', methods: ['GET'])]
    public function getAll(
        AnimalTypeRepository $animalTypeRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($animalTypeRepository, ['groups' => ['animalType']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animal-type/{animalType}', name: 'get', methods: ['GET'])]
    public function get(
        AnimalType $animalType,
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($animalType, 'json', ['groups' => ['animalType']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animal-type', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse {
        /** @var AnimalType */
        $animalType = $serializer->deserialize($request->getContent(), AnimalType::class, 'json');

        $errors = $validator->validate($animalType);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $animalType->setOwner($this->getUser());

        $entityManager->persist($animalType);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $serializer->serialize($animalType, 'json', ['groups' => ['animalType']]);
        $location = $urlGenerator->generate('api_animal_type_get', ['animalType' => $animalType->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/animal-type/{animalType}', name: 'update', methods: ['PATCH'])]
    public function update(
        AnimalType $animalType,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        /** @var AnimalType */
        $animalType = $serializer->deserialize($request->getContent(), AnimalType::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $animalType]);

        $entityManager->persist($animalType);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/animal-type/{animalType}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        AnimalType $animalType,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $entityManager->remove($animalType);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
