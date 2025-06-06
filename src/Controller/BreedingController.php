<?php

namespace App\Controller;

use App\Controller\Abstract\AbstractCachedController;
use App\Controller\Trait\ParseDtoTrait;
use App\Dto\CreateBreedingDto;
use App\Dto\UpdateBreedingDto;
use App\Entity\Animal;
use App\Entity\Breeding;
use App\Repository\BreedingRepository;
use App\Service\BreedingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('api', name: 'api_breeding_')]
final class BreedingController extends AbstractCachedController
{
    use ParseDtoTrait;

    public function __construct(
        protected readonly TagAwareCacheInterface $cache,
        protected readonly SerializerInterface $serializer,
        protected readonly EntityManagerInterface $em,
        protected readonly DenormalizerInterface $denormalizer,
        protected readonly BreedingService $breedingService,
    ) {
    }

    public static function getCacheKey(): string
    {
        return 'breedings';
    }

    #[Route('/v1/breeding', name: 'get_all', methods: ['GET'])]
    public function getAll(
        BreedingRepository $breedingRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($breedingRepository, ['groups' => ['breeding']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animal/{animal}/breeding', name: 'get_all_in', methods: ['GET'])]
    public function getAllIn(
        Animal $animal,
        BreedingRepository $breedingRepository,
    ): JsonResponse {
        $data = $this->serializer->serialize(
            [
                ...$breedingRepository->findByOwnerFlex('male', $animal->getId(), $this->getUser()),
                ...$breedingRepository->findByOwnerFlex('female', $animal->getId(), $this->getUser()),
            ],
            'json',
            ['groups' => ['breeding']],
        );

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/breeding/{breeding}', name: 'get', methods: ['GET'])]
    public function get(
        Breeding $breeding,
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($breeding, 'json', ['groups' => ['breeding']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/breeding', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        CreateBreedingDto $breedingDto,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        /** @var Breeding */
        $breeding = $this->createWithDto($breedingDto, Breeding::class);

        $this->breedingService->updateFemale($breeding, $breedingDto, $this->getUser());
        $this->breedingService->updateMale($breeding, $breedingDto, $this->getUser());

        $breeding->setOwner($this->getUser());

        $this->em->persist($breeding);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $this->serializer->serialize($breeding, 'json', ['groups' => ['breeding']]);
        $location = $urlGenerator->generate('api_breeding_get', ['breeding' => $breeding->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/breeding/{breeding}', name: 'update', methods: ['PATCH'])]
    public function update(
        Breeding $breeding,
        #[MapRequestPayload]
        UpdateBreedingDto $breedingDto,
    ): JsonResponse {
        /** @var Breeding */
        $breeding = $this->updateWithDto($breedingDto, Breeding::class, $breeding);

        $this->breedingService->updateFemale($breeding, $breedingDto, $this->getUser());
        $this->breedingService->updateMale($breeding, $breedingDto, $this->getUser());

        $this->em->persist($breeding);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/breeding/{breeding}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Breeding $breeding,
    ): JsonResponse {
        $this->em->remove($breeding);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
