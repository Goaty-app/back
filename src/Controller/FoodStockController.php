<?php

namespace App\Controller;

use App\Dto\CreateFoodStockDto;
use App\Dto\UpdateFoodStockDto;
use App\Entity\FoodStock;
use App\Entity\Herd;
use App\Repository\FoodStockRepository;
use App\Service\FoodStockService;
use App\Service\HerdService;
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

#[Route('api', name: 'api_food_stock_')]
final class FoodStockController extends AbstractCachedController
{
    use ParseDtoTrait;

    public function __construct(
        protected readonly TagAwareCacheInterface $cache,
        protected readonly SerializerInterface $serializer,
        protected readonly EntityManagerInterface $em,
        protected readonly DenormalizerInterface $denormalizer,
        protected readonly FoodStockService $foodStockService,
    ) {
    }

    public static function getCacheKey(): string
    {
        return 'foodStocks';
    }

    #[Route('/v1/food-stock', name: 'get_all', methods: ['GET'])]
    public function getAll(
        FoodStockRepository $foodStockRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($foodStockRepository, ['groups' => ['foodStock']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}/food-stock', name: 'get_all_in', methods: ['GET'])]
    public function getAllIn(
        Herd $herd,
        FoodStockRepository $foodStockRepository,
    ): JsonResponse {
        $cacheReturn = $this->getInCachedItems($foodStockRepository, 'herd', $herd->getId(), ['groups' => ['foodStock']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/food-stock/{foodStock}', name: 'get', methods: ['GET'])]
    public function get(
        FoodStock $foodStock,
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($foodStock, 'json', ['groups' => ['foodStock']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}/food-stock', name: 'create', methods: ['POST'])]
    public function create(
        Herd $herd,
        #[MapRequestPayload]
        CreateFoodStockDto $foodStockDto,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        /** @var FoodStock */
        $foodStock = $this->createWithDto($foodStockDto, FoodStock::class);

        $this->foodStockService->updateFoodStockType($foodStock, $foodStockDto, $this->getUser());

        $foodStock->setOwner($this->getUser())
            ->setHerd($herd)
            ->setQuantity(0.0)
        ;

        $this->em->persist($foodStock);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $this->serializer->serialize($foodStock, 'json', ['groups' => ['foodStock']]);
        $location = $urlGenerator->generate('api_food_stock_get', ['foodStock' => $foodStock->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/food-stock/{foodStock}', name: 'update', methods: ['PATCH'])]
    public function update(
        FoodStock $foodStock,
        #[MapRequestPayload]
        UpdateFoodStockDto $foodStockDto,
        HerdService $herdService,
    ): JsonResponse {
        /** @var FoodStock */
        $foodStock = $this->updateWithDto($foodStockDto, FoodStock::class, $foodStock);

        $herdService->updateHerd($foodStock, $foodStockDto, $this->getUser());
        $this->foodStockService->updateFoodStockType($foodStock, $foodStockDto, $this->getUser());

        $this->em->persist($foodStock);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/food-stock/{foodStock}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        FoodStock $foodStock,
    ): JsonResponse {
        $this->em->remove($foodStock);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
