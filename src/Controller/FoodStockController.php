<?php

namespace App\Controller;

use App\Entity\FoodStock;
use App\Entity\Herd;
use App\Repository\FoodStockRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('api', name: 'api_food_stock_')]
final class FoodStockController extends AbstractController
{
    #[Route('/v1/food-stock', name: 'get_all', methods: ['GET'])]
    public function getAll(
        FoodStockRepository $foodStockRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $cacheReturn = $cache->get('getAllFoodStocks', function (ItemInterface $item) use ($foodStockRepository, $serializer) {
            $item->tag('foodStocksCache');
            $data = $foodStockRepository->findByOwner($this->getUser());
            $jsonData = $serializer->serialize($data, 'json', ['groups' => ['foodStock']]);

            return $jsonData;
        });

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}/food-stock', name: 'get_all_herd', methods: ['GET'])]
    public function getAllInHerd(
        Herd $herd,
        FoodStockRepository $foodStockRepository,
        SerializerInterface $serializer,
    ): JsonResponse {
        $data = $foodStockRepository->findByOwnerFlex('herd', $herd->getId(), $this->getUser());
        $jsonData = $serializer->serialize($data, 'json', ['groups' => ['foodStock']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
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
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        /** @var FoodStock */
        $foodStock = $serializer->deserialize($request->getContent(), FoodStock::class, 'json');

        $errors = $validator->validate($foodStock);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $foodStock->setOwner($this->getUser())
            ->setHerd($herd)
            ->setQuantity(0.0)
            ->setCreatedAt(new DateTimeImmutable())
        ;

        $entityManager->persist($foodStock);
        $entityManager->flush();

        $cache->invalidateTags(['foodStocksCache']);

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
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        /** @var FoodStock */
        $production = $serializer->deserialize($request->getContent(), FoodStock::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $foodStock, AbstractNormalizer::IGNORED_ATTRIBUTES => ['quantity']]);

        $entityManager->persist($production);
        $entityManager->flush();

        $cache->invalidateTags(['foodStocksCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/food-stock/{foodStock}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        FoodStock $foodStock,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $entityManager->remove($foodStock);
        $entityManager->flush();

        $cache->invalidateTags(['foodStocksCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
