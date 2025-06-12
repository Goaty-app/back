<?php

namespace App\Controller;

use App\Controller\Abstract\AbstractCachedController;
use App\Controller\Trait\ParseDtoTrait;
use App\Dto\CreateHealthcareDto;
use App\Dto\UpdateHealthcareDto;
use App\Entity\Animal;
use App\Entity\Healthcare;
use App\Repository\HealthcareRepository;
use App\Service\HealthcareService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('api', name: 'api_healthcare_')]
final class HealthcareController extends AbstractCachedController
{
    use ParseDtoTrait;

    public function __construct(
        protected readonly TagAwareCacheInterface $cache,
        protected readonly SerializerInterface $serializer,
        protected readonly EntityManagerInterface $em,
        protected readonly DenormalizerInterface $denormalizer,
        protected readonly HealthcareService $healthcareService,
    ) {
    }

    #[Route('/v1/healthcares', name: 'get_all', methods: ['GET'])]
    public function getAll(
        HealthcareRepository $healthcareRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($healthcareRepository, ['groups' => ['healthcare']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animals/{animal}/healthcares', name: 'get_all_in', methods: ['GET'], requirements: ['animal' => '\d+'])]
    public function getAllIn(
        Animal $animal,
        HealthcareRepository $healthcareRepository,
    ): JsonResponse {
        $data = $this->serializer->serialize(
            $healthcareRepository->findByOwnerFlex('animal', $animal->getId(), $this->getUser()),
            'json',
            ['groups' => ['healthcare']],
        );

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/healthcares/{healthcare}', name: 'get', methods: ['GET'], requirements: ['healthcare' => '\d+'])]
    public function get(
        Healthcare $healthcare,
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($healthcare, 'json', ['groups' => ['healthcare']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animals/{animal}/healthcares', name: 'create', methods: ['POST'], requirements: ['animal' => '\d+'])]
    public function create(
        Animal $animal,
        #[MapRequestPayload]
        CreateHealthcareDto $healthcareDto,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        /** @var Healthcare */
        $healthcare = $this->createWithDto($healthcareDto, Healthcare::class);

        $this->healthcareService->updateHealthcareType($healthcare, $healthcareDto, $this->getUser());

        $healthcare->setOwner($this->getUser())
            ->setAnimal($animal)
            ->setCareDate(new DateTime())
        ;

        $this->em->persist($healthcare);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $this->serializer->serialize($healthcare, 'json', ['groups' => ['healthcare']]);
        $location = $urlGenerator->generate('api_healthcare_get', ['healthcare' => $healthcare->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/healthcares/{healthcare}', name: 'update', methods: ['PATCH'], requirements: ['healthcare' => '\d+'])]
    public function update(
        Healthcare $healthcare,
        #[MapRequestPayload]
        UpdateHealthcareDto $healthcareDto,
    ): JsonResponse {
        /** @var Healthcare */
        $healthcare = $this->updateWithDto($healthcareDto, Healthcare::class, $healthcare);

        $this->healthcareService->updateHealthcareType($healthcare, $healthcareDto, $this->getUser());

        $this->em->persist($healthcare);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/healthcares/{healthcare}', name: 'delete', methods: ['DELETE'], requirements: ['healthcare' => '\d+'])]
    public function delete(
        Healthcare $healthcare,
    ): JsonResponse {
        $this->em->remove($healthcare);
        $this->em->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
