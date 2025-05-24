<?php

namespace App\Controller;

use App\Entity\Herd;
use App\Entity\Production;
use App\Repository\ProductionRepository;
use App\Service\HerdService;
use App\Service\ProductionService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api', name: 'api_production_')]
final class ProductionController extends AbstractCachedController
{
    public static function getCacheKey(): string
    {
        return 'productions';
    }

    #[Route('/v1/production', name: 'get_all', methods: ['GET'])]
    public function getAll(
        ProductionRepository $productionRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($productionRepository, ['groups' => ['production']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}/production', name: 'get_all_in', methods: ['GET'])]
    public function getAllIn(
        Herd $herd,
        ProductionRepository $productionRepository,
    ): JsonResponse {
        $cacheReturn = $this->getInCachedItems($productionRepository, 'herd', $herd->getId(), ['groups' => ['production']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/production/{production}', name: 'get', methods: ['GET'])]
    public function get(
        Production $production,
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($production, 'json', ['groups' => ['production']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}/production', name: 'create', methods: ['POST'])]
    public function create(
        Herd $herd,
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        ProductionService $productionService,
    ): JsonResponse {
        /** @var Production */
        $production = $serializer->deserialize($request->getContent(), Production::class, 'json');

        $errors = $validator->validate($production);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $productionService->updateProductionType($production, $request, $this->getUser());

        $production->setOwner($this->getUser())
            ->setHerd($herd)
            ->setCreatedAt(new DateTimeImmutable())
        ;

        $entityManager->persist($production);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $serializer->serialize($production, 'json', ['groups' => ['production']]);
        $location = $urlGenerator->generate('api_production_get', ['production' => $production->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/production/{production}', name: 'update', methods: ['PATCH'])]
    public function update(
        Production $production,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        ProductionService $productionService,
        HerdService $herdService,
    ): JsonResponse {
        /** @var Production */
        $production = $serializer->deserialize($request->getContent(), Production::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $production]);

        $errors = $validator->validate($production);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $herdService->updateHerd($production, $request, $this->getUser());
        $productionService->updateProductionType($production, $request, $this->getUser());

        $entityManager->persist($production);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/production/{production}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Production $production,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $entityManager->remove($production);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
