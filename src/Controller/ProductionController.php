<?php

namespace App\Controller;

use App\Controller\Abstract\AbstractCachedController;
use App\Controller\Trait\ParseDtoTrait;
use App\Dto\CreateProductionDto;
use App\Dto\UpdateProductionDto;
use App\Entity\Herd;
use App\Entity\Production;
use App\Repository\ProductionRepository;
use App\Service\HerdService;
use App\Service\ProductionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('api', name: 'api_production_')]
final class ProductionController extends AbstractCachedController
{
    use ParseDtoTrait;

    public function __construct(
        protected readonly TagAwareCacheInterface $cache,
        protected readonly SerializerInterface $serializer,
        protected readonly EntityManagerInterface $em,
        protected readonly DenormalizerInterface $denormalizer,
        protected readonly ProductionService $productionService,
    ) {
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
        $data = $this->serializer->serialize(
            $productionRepository->findByOwnerFlex('herd', $herd->getId(), $this->getUser()),
            'json',
            ['groups' => ['production']],
        );

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/production/{production}', name: 'get', methods: ['GET'])]
    public function get(
        Production $production,
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($production, 'json', ['groups' => ['production']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}/production', name: 'create', methods: ['POST'])]
    public function create(
        Herd $herd,
        #[MapRequestPayload]
        CreateProductionDto $productionDto,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        /** @var Production */
        $production = $this->createWithDto($productionDto, Production::class);

        $this->productionService->updateProductionType($production, $productionDto, $this->getUser());

        $production->setOwner($this->getUser())
            ->setHerd($herd)
        ;

        $this->em->persist($production);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $this->serializer->serialize($production, 'json', ['groups' => ['production']]);
        $location = $urlGenerator->generate('api_production_get', ['production' => $production->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/production/{production}', name: 'update', methods: ['PATCH'])]
    public function update(
        Production $production,
        #[MapRequestPayload]
        UpdateProductionDto $productionDto,
        HerdService $herdService,
    ): JsonResponse {
        /** @var Production */
        $production = $this->updateWithDto($productionDto, Production::class, $production);

        $herdService->updateHerd($production, $productionDto, $this->getUser());
        $this->productionService->updateProductionType($production, $productionDto, $this->getUser());

        $this->em->persist($production);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/production/{production}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Production $production,
    ): JsonResponse {
        $this->em->remove($production);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
