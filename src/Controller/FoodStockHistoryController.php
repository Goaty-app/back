<?php

namespace App\Controller;

use App\Dto\CreateFoodStockHistoryDto;
use App\Entity\FoodStock;
use App\Entity\FoodStockHistory;
use App\Enum\Operation;
use App\Repository\FoodStockHistoryRepository;
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

#[Route('api', name: 'api_food_stock_history_')]
final class FoodStockHistoryController extends AbstractCachedController
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
        return 'foodStockHistories';
    }

    #[Route('/v1/food-stock/{foodStock}/food-stock-history', name: 'get_all_in', methods: ['GET'])]
    public function getAll(
        FoodStock $foodStock,
        FoodStockHistoryRepository $foodStockHistory,
    ): JsonResponse {
        $data = $this->serializer->serialize(
            $foodStockHistory->findByOwnerFlex('foodStock', $foodStock->getId(), $this->getUser()),
            'json',
            ['groups' => ['foodStockHistory']],
        );

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/food-stock-history/{foodStockHistory}', name: 'get', methods: ['GET'])]
    public function get(
        FoodStockHistory $foodStockHistory,
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($foodStockHistory, 'json', ['groups' => ['foodStockHistory']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/food-stock/{foodStock}/food-stock-history', name: 'create', methods: ['POST'])]
    public function create(
        FoodStock $foodStock,
        #[MapRequestPayload]
        CreateFoodStockHistoryDto $foodStockHistoryDto,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        /** @var FoodStockHistory */
        $foodStockHistory = $this->createWithDto($foodStockHistoryDto, FoodStockHistory::class);

        match ($foodStockHistory->getOperation()) {
            Operation::PLUS  => $foodStock->setQuantity($foodStock->getQuantity() + $foodStockHistory->getQuantity()),
            Operation::MINUS => $foodStock->setQuantity($foodStock->getQuantity() - $foodStockHistory->getQuantity()),
        };

        $this->em->persist($foodStock);

        $foodStockHistory->setOwner($this->getUser())
            ->setFoodStock($foodStock)
        ;

        $this->em->persist($foodStockHistory);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(FoodStockController::getCacheKey()),
        ]);

        $jsonData = $this->serializer->serialize($foodStockHistory, 'json', ['groups' => ['foodStockHistory']]);
        $location = $urlGenerator->generate('api_food_stock_history_get', ['foodStockHistory' => $foodStockHistory->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/food-stock-history/{foodStockHistory}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        FoodStockHistory $foodStockHistory,
    ): JsonResponse {
        $foodStock = $foodStockHistory->getFoodStock();

        match ($foodStockHistory->getOperation()) {
            Operation::PLUS  => $foodStock->setQuantity($foodStock->getQuantity() - $foodStockHistory->getQuantity()),
            Operation::MINUS => $foodStock->setQuantity($foodStock->getQuantity() + $foodStockHistory->getQuantity()),
        };

        $this->em->persist($foodStock);

        $this->em->remove($foodStockHistory);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(FoodStockController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
