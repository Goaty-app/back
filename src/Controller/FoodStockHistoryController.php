<?php

namespace App\Controller;

use App\Entity\FoodStock;
use App\Entity\FoodStockHistory;
use App\Enum\Operation;
use App\Repository\FoodStockHistoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api', name: 'api_food_stock_history_')]
final class FoodStockHistoryController extends AbstractCachedController
{
    public static function getCacheKey(): string
    {
        return 'foodStockHistories';
    }

    public static function getGroupCacheKey(): string
    {
        return FoodStockController::getGroupCacheKey();
    }

    #[Route('/v1/food-stock/{foodStock}/food-stock-history', name: 'get_all_in', methods: ['GET'])]
    public function getAll(
        FoodStock $foodStock,
        FoodStockHistoryRepository $foodStockHistory,
    ): JsonResponse {
        $cacheReturn = $this->getInCachedItems($foodStockHistory, 'foodStock', $foodStock->getId(), ['groups' => ['foodStockHistory']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/food-stock-history/{foodStockHistory}', name: 'get', methods: ['GET'])]
    public function get(
        FoodStockHistory $foodStockHistory,
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($foodStockHistory, 'json', ['groups' => ['foodStockHistory']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/food-stock/{foodStock}/food-stock-history', name: 'create', methods: ['POST'])]
    public function create(
        FoodStock $foodStock,
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse {
        /** @var FoodStockHistory */
        $foodStockHistory = $serializer->deserialize($request->getContent(), FoodStockHistory::class, 'json');

        $errors = $validator->validate($foodStockHistory);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        match ($foodStockHistory->getOperation()) {
            Operation::PLUS  => $foodStock->setQuantity($foodStock->getQuantity() + $foodStockHistory->getQuantity()),
            Operation::MINUS => $foodStock->setQuantity($foodStock->getQuantity() - $foodStockHistory->getQuantity()),
        };

        $entityManager->persist($foodStock);

        $foodStockHistory->setOwner($this->getUser())
            ->setCreatedAt(new DateTimeImmutable())
            ->setFoodStock($foodStock)
        ;

        $entityManager->persist($foodStockHistory);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        $jsonData = $serializer->serialize($foodStockHistory, 'json', ['groups' => ['foodStockHistory']]);
        $location = $urlGenerator->generate('api_food_stock_history_get', ['foodStockHistory' => $foodStockHistory->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/food-stock-history/{foodStockHistory}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        FoodStockHistory $foodStockHistory,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $foodStock = $foodStockHistory->getFoodStock();

        match ($foodStockHistory->getOperation()) {
            Operation::PLUS  => $foodStock->setQuantity($foodStock->getQuantity() - $foodStockHistory->getQuantity()),
            Operation::MINUS => $foodStock->setQuantity($foodStock->getQuantity() + $foodStockHistory->getQuantity()),
        };

        $entityManager->persist($foodStock);

        $entityManager->remove($foodStockHistory);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
