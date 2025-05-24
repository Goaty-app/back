<?php

namespace App\Controller;

use App\Entity\FoodStock;
use App\Entity\Herd;
use App\Repository\FoodStockRepository;
use App\Service\FoodStockService;
use App\Service\HerdService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api', name: 'api_food_stock_')]
final class FoodStockController extends AbstractCachedController
{
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
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($foodStock, 'json', ['groups' => ['foodStock']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}/food-stock', name: 'create', methods: ['POST'])]
    public function create(
        Herd $herd,
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        FoodStockService $foodStockService,
    ): JsonResponse {
        /** @var FoodStock */
        $foodStock = $serializer->deserialize($request->getContent(), FoodStock::class, 'json');

        $errors = $validator->validate($foodStock);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $foodStockService->updateFoodStockType($foodStock, $request, $this->getUser());

        $foodStock->setOwner($this->getUser())
            ->setHerd($herd)
            ->setQuantity(0.0)
        ;

        $entityManager->persist($foodStock);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $serializer->serialize($foodStock, 'json', ['groups' => ['foodStock']]);
        $location = $urlGenerator->generate('api_food_stock_get', ['foodStock' => $foodStock->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/food-stock/{foodStock}', name: 'update', methods: ['PATCH'])]
    public function update(
        FoodStock $foodStock,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        FoodStockService $foodStockService,
        ValidatorInterface $validator,
        HerdService $herdService,
    ): JsonResponse {
        /** @var FoodStock */
        $foodStock = $serializer->deserialize($request->getContent(), FoodStock::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $foodStock, AbstractNormalizer::IGNORED_ATTRIBUTES => ['quantity']]);

        $errors = $validator->validate($foodStock);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $herdService->updateHerd($foodStock, $request, $this->getUser());
        $foodStockService->updateFoodStockType($foodStock, $request, $this->getUser());

        $entityManager->persist($foodStock);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/food-stock/{foodStock}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        FoodStock $foodStock,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $entityManager->remove($foodStock);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
