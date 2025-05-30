<?php

namespace App\Controller;

use App\Dto\CreateProductionTypeDto;
use App\Dto\UpdateProductionTypeDto;
use App\Entity\ProductionType;
use App\Repository\ProductionTypeRepository;
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

#[Route('api', name: 'api_production_type_')]
final class ProductionTypeController extends AbstractCachedController
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
        return 'productionTypes';
    }

    public static function getGroupCacheKey(): string
    {
        return ProductionController::getGroupCacheKey();
    }

    #[Route('/v1/production-type', name: 'get_all', methods: ['GET'])]
    public function getAll(
        ProductionTypeRepository $productionTypeRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($productionTypeRepository, ['groups' => ['productionType']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/production-type/{productionType}', name: 'get', methods: ['GET'])]
    public function get(
        ProductionType $productionType,
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($productionType, 'json', ['groups' => ['productionType']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/production-type', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        CreateProductionTypeDto $productionTypeDto,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        /** @var ProductionType */
        $productionType = $this->createWithDto($productionTypeDto, ProductionType::class);

        $productionType->setOwner($this->getUser());

        $this->em->persist($productionType);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $this->serializer->serialize($productionType, 'json', ['groups' => ['productionType']]);
        $location = $urlGenerator->generate('api_production_type_get', ['productionType' => $productionType->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/production-type/{productionType}', name: 'update', methods: ['PATCH'])]
    public function update(
        ProductionType $productionType,
        #[MapRequestPayload]
        UpdateProductionTypeDto $productionTypeDto,
    ): JsonResponse {
        /** @var ProductionType */
        $productionType = $this->updateWithDto($productionTypeDto, ProductionType::class, $productionType);

        $this->em->persist($productionType);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/production-type/{productionType}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        ProductionType $productionType,
    ): JsonResponse {
        $this->em->remove($productionType);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
