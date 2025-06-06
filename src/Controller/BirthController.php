<?php

namespace App\Controller;

use App\Controller\Abstract\AbstractCachedController;
use App\Controller\Trait\ParseDtoTrait;
use App\Dto\CreateBirthDto;
use App\Dto\UpdateBirthDto;
use App\Entity\Birth;
use App\Repository\BirthRepository;
use App\Service\BirthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('api', name: 'api_birth_')]
final class BirthController extends AbstractCachedController
{
    use ParseDtoTrait;

    public function __construct(
        protected readonly TagAwareCacheInterface $cache,
        protected readonly SerializerInterface $serializer,
        protected readonly EntityManagerInterface $em,
        protected readonly DenormalizerInterface $denormalizer,
        protected readonly BirthService $birthService,
    ) {
    }

    public static function getCacheKey(): string
    {
        return 'births';
    }

    #[Route('/v1/birth', name: 'get_all', methods: ['GET'])]
    public function getAll(
        BirthRepository $birthRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($birthRepository, ['groups' => ['birth']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/birth/{birth}', name: 'get', methods: ['GET'])]
    public function get(
        Birth $birth,
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($birth, 'json', ['groups' => ['birth']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/birth', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        CreateBirthDto $birthDto,
        UrlGeneratorInterface $urlGenerator,
        BirthService $birthService,
    ): JsonResponse {
        /** @var Birth */
        $birth = $this->createWithDto($birthDto, Birth::class);

        $birthService->updateChild($birth, $birthDto, $this->getUser());
        $birthService->updateBreeding($birth, $birthDto, $this->getUser());

        $birth->setOwner($this->getUser());

        $this->em->persist($birth);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(AnimalController::getCacheKey()),
        ]);

        $jsonData = $this->serializer->serialize($birth, 'json', ['groups' => ['birth']]);
        $location = $urlGenerator->generate('api_birth_get', ['birth' => $birth->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/birth/{birth}', name: 'update', methods: ['PATCH'])]
    public function update(
        Birth $birth,
        #[MapRequestPayload]
        UpdateBirthDto $birthDto,
        BirthService $birthService,
    ): JsonResponse {
        /** @var Birth */
        $birth = $this->updateWithDto($birthDto, Birth::class, $birth);

        $birthService->updateChild($birth, $birthDto, $this->getUser());
        $birthService->updateBreeding($birth, $birthDto, $this->getUser());

        $this->em->persist($birth);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(AnimalController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/birth/{birth}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Birth $birth,
    ): JsonResponse {
        $this->em->remove($birth);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(AnimalController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
