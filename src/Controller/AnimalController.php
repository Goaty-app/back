<?php

namespace App\Controller;

use App\Dto\CreateAnimalDto;
use App\Dto\UpdateAnimalDto;
use App\Entity\Animal;
use App\Entity\Herd;
use App\Repository\AnimalRepository;
use App\Service\AnimalService;
use App\Service\HerdService;
use App\Service\PaginationService;
use App\Trait\ParseDtoTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('api', name: 'api_animal_')]
final class AnimalController extends AbstractCachedController
{
    use ParseDtoTrait;

    public const GROUPS = [
        'groups' => ['animal'],
    ];

    public function __construct(
        protected readonly TagAwareCacheInterface $cache,
        protected readonly SerializerInterface $serializer,
        protected readonly EntityManagerInterface $em,
        protected readonly DenormalizerInterface $denormalizer,
        protected readonly AnimalService $animalService,
        protected readonly PaginationService $paginationService,
    ) {
    }

    public static function getCacheKey(): string
    {
        return 'animals';
    }

    #[Route('/v1/animal', name: 'get_all', methods: ['GET'])]
    public function getAll(
        AnimalRepository $animalRepository,
        Request $request,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($animalRepository, self::GROUPS);

        $paginatedResponse = $this->paginationService->paginate($cacheReturn, $request);

        $paginatedResponse = $this->serializer->serialize($paginatedResponse, 'json', self::GROUPS);

        return new JsonResponse($paginatedResponse, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}/animal', name: 'get_all_in', methods: ['GET'])]
    public function getAllIn(
        Herd $herd,
        AnimalRepository $animalRepository,
        Request $request,
    ): JsonResponse {
        $data = $this->serializer->serialize(
            $animalRepository->findByOwnerFlex('herd', $herd->getId(), $this->getUser()),
            'json',
            self::GROUPS,
        );

        $paginatedResponse = $this->paginationService->paginate($data, $request);

        $paginatedResponse = $this->serializer->serialize($paginatedResponse, 'json', self::GROUPS);

        return new JsonResponse($paginatedResponse, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animal/{animal}', name: 'get', methods: ['GET'])]
    public function get(
        Animal $animal,
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($animal, 'json', self::GROUPS);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}/animal', name: 'create', methods: ['POST'])]
    public function create(
        Herd $herd,
        #[MapRequestPayload]
        CreateAnimalDto $animalDto,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        /** @var Animal */
        $animal = $this->createWithDto($animalDto, Animal::class);

        $this->animalService->updateAnimalType($animal, $animalDto, $this->getUser());

        $animal->setOwner($this->getUser())
            ->setHerd($herd)
        ;

        $this->em->persist($animal);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $this->serializer->serialize($animal, 'json', self::GROUPS);
        $location = $urlGenerator->generate('api_animal_get', ['animal' => $animal->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/animal/{animal}', name: 'update', methods: ['PATCH'])]
    public function update(
        Animal $animal,
        #[MapRequestPayload]
        UpdateAnimalDto $animalDto,
        HerdService $herdService,
    ): JsonResponse {
        /** @var Animal */
        $animal = $this->updateWithDto($animalDto, Animal::class, $animal);

        $herdService->updateHerd($animal, $animalDto, $this->getUser());
        $this->animalService->updateAnimalType($animal, $animalDto, $this->getUser());

        $this->em->persist($animal);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(HealthcareController::getCacheKey()),
            $this->getTag(BreedingController::getCacheKey()),
            $this->getTag(BirthController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/animal/{animal}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Animal $animal,
    ): JsonResponse {
        $this->em->remove($animal);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(HealthcareController::getCacheKey()),
            $this->getTag(BreedingController::getCacheKey()),
            $this->getTag(BirthController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
