<?php

namespace App\Controller;

use App\Entity\HealthcareType;
use App\Repository\HealthcareTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api', name: 'api_healthcare_type_')]
final class HealthcareTypeController extends AbstractCachedController
{
    public static function getCacheKey(): string
    {
        return 'HealthcareTypes';
    }

    public static function getGroupCacheKey(): string
    {
        return HealthcareController::getGroupCacheKey();
    }

    #[Route('/v1/healthcare-type', name: 'get_all', methods: ['GET'])]
    public function getAll(
        HealthcareTypeRepository $healthcareTypeRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($healthcareTypeRepository, ['groups' => ['healthcareType']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/healthcare-type/{healthcareType}', name: 'get', methods: ['GET'])]
    public function get(
        HealthcareType $healthcareType,
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($healthcareType, 'json', ['groups' => ['healthcareType']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/healthcare-type', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse {
        /** @var HealthcareType */
        $healthcareType = $serializer->deserialize($request->getContent(), HealthcareType::class, 'json');

        $errors = $validator->validate($healthcareType);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $healthcareType->setOwner($this->getUser());

        $entityManager->persist($healthcareType);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $serializer->serialize($healthcareType, 'json', ['groups' => ['healthcareType']]);
        $location = $urlGenerator->generate('api_healthcare_type_get', ['healthcareType' => $healthcareType->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/healthcare-type/{healthcareType}', name: 'update', methods: ['PATCH'])]
    public function update(
        HealthcareType $healthcareType,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse {
        /** @var HealthcareType */
        $healthcareType = $serializer->deserialize($request->getContent(), HealthcareType::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $healthcareType]);

        $errors = $validator->validate($healthcareType);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($healthcareType);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/healthcare-type/{healthcareType}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        HealthcareType $healthcareType,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $entityManager->remove($healthcareType);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
