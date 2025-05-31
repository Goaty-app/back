<?php

namespace App\Controller;

use App\Dto\CreateAnimalTypeDto;
use App\Dto\UpdateAnimalTypeDto;
use App\Entity\AnimalType;
use App\Repository\AnimalTypeRepository;
use App\Trait\ParseDtoTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('api', name: 'api_animal_type_')]
final class AnimalTypeController extends AbstractCachedController
{
    use ParseDtoTrait;

    public function __construct(
        protected readonly TagAwareCacheInterface $cache,
        protected readonly SerializerInterface $serializer,
        protected readonly EntityManagerInterface $em,
        protected readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public static function getCacheKey(): string
    {
        return 'animalTypes';
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
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($animalType, 'json', ['groups' => ['animalType']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animal-type', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        CreateAnimalTypeDto $animalTypeDto,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        /** @var AnimalType */
        $animalType = $this->createWithDto($animalTypeDto, AnimalType::class);

        $animalType->setOwner($this->getUser());

        $this->em->persist($animalType);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $this->serializer->serialize($animalType, 'json', ['groups' => ['animalType']]);
        $location = $urlGenerator->generate('api_animal_type_get', ['animalType' => $animalType->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/animal-type/{animalType}', name: 'update', methods: ['PATCH'])]
    public function update(
        AnimalType $animalType,
        #[MapRequestPayload]
        UpdateAnimalTypeDto $animalTypeDto,
    ): JsonResponse {
        /** @var AnimalType */
        $animalType = $this->updateWithDto($animalTypeDto, AnimalType::class, $animalType);

        $this->em->persist($animalType);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(AnimalController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/animal-type/{animalType}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        AnimalType $animalType,
    ): JsonResponse {
        $this->em->remove($animalType);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(AnimalController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
