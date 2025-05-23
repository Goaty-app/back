<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\Healthcare;
use App\Repository\HealthcareRepository;
use App\Service\HealthcareService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api', name: 'api_healthcare_')]
final class HealthcareController extends AbstractCachedController
{
    public static function getCacheKey(): string
    {
        return 'healthcares';
    }

    #[Route('/v1/healthcare', name: 'get_all', methods: ['GET'])]
    public function getAll(
        HealthcareRepository $healthcareRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($healthcareRepository, ['groups' => ['healthcare']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animal/{animal}/healthcare', name: 'get_all_herd', methods: ['GET'])]
    public function getAllInAnimal(
        Animal $animal,
        HealthcareRepository $healthcareRepository,
    ): JsonResponse {
        $cacheReturn = $this->getInCachedItems($healthcareRepository, 'animal', $animal->getId(), ['groups' => ['healthcare']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/healthcare/{healthcare}', name: 'get', methods: ['GET'])]
    public function get(
        Healthcare $healthcare,
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($healthcare, 'json', ['groups' => ['healthcare']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animal/{animal}/healthcare', name: 'create', methods: ['POST'])]
    public function create(
        Animal $animal,
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        HealthcareService $healthcareService,
    ): JsonResponse {
        /** @var Healthcare */
        $healthcare = $serializer->deserialize($request->getContent(), Healthcare::class, 'json');

        $errors = $validator->validate($healthcare);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $healthcareService->updateHealthcareType($healthcare, $request, $this->getUser());

        $healthcare->setOwner($this->getUser())
            ->setAnimal($animal)
            ->setCareDate(new DateTime())
        ;

        $entityManager->persist($healthcare);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $serializer->serialize($healthcare, 'json', ['groups' => ['healthcare']]);
        $location = $urlGenerator->generate('api_healthcare_get', ['healthcare' => $healthcare->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/healthcare/{healthcare}', name: 'update', methods: ['PATCH'])]
    public function update(
        Healthcare $healthcare,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        HealthcareService $healthcareService,
    ): JsonResponse {
        /** @var Healthcare */
        $healthcare = $serializer->deserialize($request->getContent(), Healthcare::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $healthcare]);

        $healthcareService->updateHealthcareType($healthcare, $request, $this->getUser());

        $entityManager->persist($healthcare);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/healthcare/{healthcare}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Healthcare $healthcare,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $entityManager->remove($healthcare);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
