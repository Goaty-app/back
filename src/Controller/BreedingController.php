<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\Breeding;
use App\Entity\Interface\OwnedEntityRepository;
use App\Repository\BreedingRepository;
use App\Service\BreedingService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api', name: 'api_breeding_')]
final class BreedingController extends AbstractCachedController
{
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
        $cacheReturn = $this->getInCachedItemsCustomRequest(
            $breedingRepository,
            $animal->getId(),
            fn (OwnedEntityRepository $repository, int $value) => [
                ...$repository->findByOwnerFlex('male', $value, $this->getUser()),
                ...$repository->findByOwnerFlex('female', $value, $this->getUser()),
            ],
            ['groups' => ['breeding']],
        );

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/breeding/{breeding}', name: 'get', methods: ['GET'])]
    public function get(
        Breeding $breeding,
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($breeding, 'json', ['groups' => ['breeding']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/breeding', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        BreedingService $breedingService,
    ): JsonResponse {
        /** @var Breeding */
        $breeding = $serializer->deserialize($request->getContent(), Breeding::class, 'json');

        $errors = $validator->validate($breeding);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $breedingService->updateFemale($breeding, $request, $this->getUser());
        $breedingService->updateMale($breeding, $request, $this->getUser());

        $breeding->setOwner($this->getUser())
            ->setCreatedAt(new DateTimeImmutable())
        ;

        $entityManager->persist($breeding);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $serializer->serialize($breeding, 'json', ['groups' => ['breeding']]);
        $location = $urlGenerator->generate('api_breeding_get', ['breeding' => $breeding->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/breeding/{breeding}', name: 'update', methods: ['PATCH'])]
    public function update(
        Breeding $breeding,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        BreedingService $breedingService,
    ): JsonResponse {
        /** @var Breeding */
        $breeding = $serializer->deserialize($request->getContent(), Breeding::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $breeding]);

        $breedingService->updateFemale($breeding, $request, $this->getUser());
        $breedingService->updateMale($breeding, $request, $this->getUser());

        $entityManager->persist($breeding);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/breeding/{breeding}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Breeding $breeding,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $entityManager->remove($breeding);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
