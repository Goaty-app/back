<?php

namespace App\Controller;

use App\Entity\FoodStockType;
use App\Repository\FoodStockTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api', name: 'api_food_stock_type_')]
final class FoodStockTypeController extends AbstractCachedController
{
    public static function getCacheKey(): string
    {
        return 'foodStockTypes';
    }

    public static function getGroupCacheKey(): string
    {
        return FoodStockController::getGroupCacheKey();
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
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($foodStockType, 'json', ['groups' => ['foodStockType']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/food-stock-type', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse {
        /** @var FoodStockType */
        $foodStockType = $serializer->deserialize($request->getContent(), FoodStockType::class, 'json');

        $errors = $validator->validate($foodStockType);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $foodStockType->setOwner($this->getUser());

        $entityManager->persist($foodStockType);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $serializer->serialize($foodStockType, 'json', ['groups' => ['foodStock']]);
        $location = $urlGenerator->generate('api_food_stock_type_get', ['foodStockType' => $foodStockType->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/food-stock-type/{foodStockType}', name: 'update', methods: ['PATCH'])]
    public function update(
        FoodStockType $foodStockType,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse {
        /** @var FoodStockType */
        $foodStockType = $serializer->deserialize($request->getContent(), FoodStockType::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $foodStockType]);

        $errors = $validator->validate($foodStockType);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($foodStockType);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/food-stock-type/{foodStockType}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        FoodStockType $foodStockType,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $entityManager->remove($foodStockType);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
