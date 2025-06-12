<?php

namespace App\Controller;

use App\Controller\Abstract\AbstractCachedController;
use App\Controller\Trait\ParseDtoTrait;
use App\Dto\CreateHerdDto;
use App\Dto\UpdateHerdDto;
use App\Entity\Herd;
use App\Repository\HerdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('api', name: 'api_herd_')]
final class HerdController extends AbstractCachedController
{
    use ParseDtoTrait;

    public function __construct(
        protected readonly TagAwareCacheInterface $cache,
        protected readonly SerializerInterface $serializer,
        protected readonly EntityManagerInterface $em,
        protected readonly DenormalizerInterface $denormalizer,
    ) {
    }

    #[Route('/v1/herds', name: 'get_all', methods: ['GET'])]
    public function getAll(
        HerdRepository $herdRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($herdRepository, ['groups' => ['herd']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herds/{herd}', name: 'get', methods: ['GET'], requirements: ['herd' => '\d+'])]
    public function get(
        Herd $herd,
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($herd, 'json', ['groups' => ['herd']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herds', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        CreateHerdDto $herdDto,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        /** @var Herd */
        $herd = $this->createWithDto($herdDto, Herd::class);

        $herd->setOwner($this->getUser());

        $this->em->persist($herd);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $this->serializer->serialize($herd, 'json', ['groups' => ['herd']]);
        $location = $urlGenerator->generate('api_herd_get', ['herd' => $herd->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/herds/{herd}', name: 'update', methods: ['PATCH'], requirements: ['herd' => '\d+'])]
    public function update(
        Herd $herd,
        #[MapRequestPayload]
        UpdateHerdDto $herdDto,
    ): JsonResponse {
        /** @var Herd */
        $herd = $this->updateWithDto($herdDto, Herd::class, $herd);

        $this->em->persist($herd);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getUserTag(),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/herds/{herd}', name: 'delete', methods: ['DELETE'], requirements: ['herd' => '\d+'])]
    public function delete(
        Herd $herd,
    ): JsonResponse {
        $this->em->remove($herd);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getUserTag(),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
