<?php

namespace App\Controller;

use App\Dto\CreateFoodStockTypeDto;
use App\Dto\UpdateFoodStockTypeDto;
use App\Entity\FoodStockType;
use App\Repository\FoodStockTypeRepository;
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

#[Route('api', name: 'api_food_stock_type_')]
final class FoodStockTypeController extends AbstractCachedController
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
        return 'foodStockTypes';
    }

    #[Route('/v1/food-stock-type', name: 'get_all', methods: ['GET'])]
    public function getAll(
        FoodStockTypeRepository $foodStockTypeRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($foodStockTypeRepository, ['groups' => ['foodStockType']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/food-stock-type/{foodStockType}', name: 'get', methods: ['GET'])]
    public function get(
        FoodStockType $foodStockType,
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($foodStockType, 'json', ['groups' => ['foodStockType']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/food-stock-type', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        CreateFoodStockTypeDto $foodStockTypeDto,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        /** @var FoodStockType */
        $foodStockType = $this->createWithDto($foodStockTypeDto, FoodStockType::class);

        $foodStockType->setOwner($this->getUser());

        $this->em->persist($foodStockType);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $this->serializer->serialize($foodStockType, 'json', ['groups' => ['foodStock']]);
        $location = $urlGenerator->generate('api_food_stock_type_get', ['foodStockType' => $foodStockType->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/food-stock-type/{foodStockType}', name: 'update', methods: ['PATCH'])]
    public function update(
        FoodStockType $foodStockType,
        #[MapRequestPayload]
        UpdateFoodStockTypeDto $foodStockTypeDto,
    ): JsonResponse {
        /** @var FoodStockType */
        $foodStockType = $this->updateWithDto($foodStockTypeDto, FoodStockType::class, $foodStockType);

        $this->em->persist($foodStockType);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(FoodStockController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/food-stock-type/{foodStockType}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        FoodStockType $foodStockType,
    ): JsonResponse {
        $this->em->remove($foodStockType);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(FoodStockController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
