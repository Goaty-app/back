<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\Herd;
use App\Repository\AnimalRepository;
use App\Service\AnimalService;
use App\Service\HerdService;
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

#[Route('api', name: 'api_animal_')]
final class AnimalController extends AbstractCachedController
{
    public static function getCacheKey(): string
    {
        return 'animals';
    }

    #[Route('/v1/animal', name: 'get_all', methods: ['GET'])]
    public function getAll(
        AnimalRepository $animalRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($animalRepository, ['groups' => ['animal']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}/animal', name: 'get_all_herd', methods: ['GET'])]
    public function getAllInHerd(
        Herd $herd,
        AnimalRepository $animalRepository,
    ): JsonResponse {
        $cacheReturn = $this->getInCachedItems($animalRepository, 'herd', $herd->getId(), ['groups' => ['animal']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animal/{animal}', name: 'get', methods: ['GET'])]
    public function get(
        Animal $animal,
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($animal, 'json', ['groups' => ['animal']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}/animal', name: 'create', methods: ['POST'])]
    public function create(
        Herd $herd,
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        AnimalService $animalService,
    ): JsonResponse {
        /** @var Animal */
        $animal = $serializer->deserialize($request->getContent(), Animal::class, 'json');

        $errors = $validator->validate($animal);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $animalService->updateAnimalType($animal, $request, $this->getUser());

        $animal->setOwner($this->getUser())
            ->setHerd($herd)
            ->setCreatedAt(new DateTimeImmutable())
        ;

        $entityManager->persist($animal);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $serializer->serialize($animal, 'json', ['groups' => ['animal']]);
        $location = $urlGenerator->generate('api_animal_get', ['animal' => $animal->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/animal/{animal}', name: 'update', methods: ['PATCH'])]
    public function update(
        Animal $animal,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        AnimalService $animalService,
        HerdService $herdService,
    ): JsonResponse {
        /** @var Animal */
        $animal = $serializer->deserialize($request->getContent(), Animal::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $animal]);

        $herdService->updateHerd($animal, $request, $this->getUser());
        $animalService->updateAnimalType($animal, $request, $this->getUser());

        $entityManager->persist($animal);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/animal/{animal}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Animal $animal,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $entityManager->remove($animal);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
