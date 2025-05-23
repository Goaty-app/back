<?php

namespace App\Controller;

use App\Entity\ProductionType;
use App\Repository\ProductionTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api', name: 'api_production_type_')]
final class ProductionTypeController extends AbstractCachedController
{
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
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($productionType, 'json', ['groups' => ['productionType']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/production-type', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse {
        /** @var ProductionType */
        $productionType = $serializer->deserialize($request->getContent(), ProductionType::class, 'json');

        $errors = $validator->validate($productionType);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $productionType->setOwner($this->getUser());

        $entityManager->persist($productionType);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $serializer->serialize($productionType, 'json', ['groups' => ['productionType']]);
        $location = $urlGenerator->generate('api_production_type_get', ['productionType' => $productionType->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/production-type/{productionType}', name: 'update', methods: ['PATCH'])]
    public function update(
        ProductionType $productionType,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse {
        /** @var ProductionType */
        $productionType = $serializer->deserialize($request->getContent(), ProductionType::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $productionType]);

        $errors = $validator->validate($productionType);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($productionType);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/production-type/{productionType}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        ProductionType $productionType,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $entityManager->remove($productionType);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getGroupCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
