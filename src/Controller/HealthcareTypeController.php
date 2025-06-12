<?php

namespace App\Controller;

use App\Controller\Abstract\AbstractCachedController;
use App\Controller\Trait\ParseDtoTrait;
use App\Dto\CreateHealthcareTypeDto;
use App\Dto\UpdateHealthcareTypeDto;
use App\Entity\HealthcareType;
use App\Repository\HealthcareTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('api', name: 'api_healthcare_type_')]
final class HealthcareTypeController extends AbstractCachedController
{
    use ParseDtoTrait;

    public function __construct(
        protected readonly TagAwareCacheInterface $cache,
        protected readonly SerializerInterface $serializer,
        protected readonly EntityManagerInterface $em,
        protected readonly DenormalizerInterface $denormalizer,
    ) {
    }

    #[Route('/v1/healthcare-types', name: 'get_all', methods: ['GET'])]
    public function getAll(
        HealthcareTypeRepository $healthcareTypeRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($healthcareTypeRepository, ['groups' => ['healthcareType']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/healthcare-types/{healthcareType}', name: 'get', methods: ['GET'], requirements: ['healthcareType' => '\d+'])]
    public function get(
        HealthcareType $healthcareType,
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($healthcareType, 'json', ['groups' => ['healthcareType']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/healthcare-types', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        CreateHealthcareTypeDto $healthcareTypeDto,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        /** @var HealthcareType */
        $healthcareType = $this->createWithDto($healthcareTypeDto, HealthcareType::class);

        $healthcareType->setOwner($this->getUser());

        $this->em->persist($healthcareType);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $this->serializer->serialize($healthcareType, 'json', ['groups' => ['healthcareType']]);
        $location = $urlGenerator->generate('api_healthcare_type_get', ['healthcareType' => $healthcareType->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/healthcare-types/{healthcareType}', name: 'update', methods: ['PATCH'], requirements: ['healthcareType' => '\d+'])]
    public function update(
        HealthcareType $healthcareType,
        #[MapRequestPayload]
        UpdateHealthcareTypeDto $healthcareTypeDto,
    ): JsonResponse {
        /** @var HealthcareType */
        $healthcareType = $this->updateWithDto($healthcareTypeDto, HealthcareType::class, $healthcareType);

        $this->em->persist($healthcareType);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(HealthcareController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/healthcare-types/{healthcareType}', name: 'delete', methods: ['DELETE'], requirements: ['healthcareType' => '\d+'])]
    public function delete(
        HealthcareType $healthcareType,
    ): JsonResponse {
        $this->em->remove($healthcareType);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(HealthcareController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
