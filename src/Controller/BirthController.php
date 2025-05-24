<?php

namespace App\Controller;

use App\Entity\Birth;
use App\Repository\BirthRepository;
use App\Service\BirthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api', name: 'api_birth_')]
final class BirthController extends AbstractCachedController
{
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
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($birth, 'json', ['groups' => ['birth']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/birth', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        BirthService $birthService,
    ): JsonResponse {
        /** @var Birth */
        $birth = $serializer->deserialize($request->getContent(), Birth::class, 'json');

        $errors = $validator->validate($birth);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $birthService->updateChild($birth, $request, $this->getUser());
        $birthService->updateBreeding($birth, $request, $this->getUser());

        $birth->setOwner($this->getUser());

        $entityManager->persist($birth);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $serializer->serialize($birth, 'json', ['groups' => ['birth']]);
        $location = $urlGenerator->generate('api_birth_get', ['birth' => $birth->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/birth/{birth}', name: 'update', methods: ['PATCH'])]
    public function update(
        Birth $birth,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        BirthService $birthService,
    ): JsonResponse {
        /** @var Birth */
        $birth = $serializer->deserialize($request->getContent(), Birth::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $birth]);

        $birthService->updateChild($birth, $request, $this->getUser());
        $birthService->updateBreeding($birth, $request, $this->getUser());

        $entityManager->persist($birth);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(AnimalController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/birth/{birth}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Birth $birth,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $entityManager->remove($birth);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
            $this->getTag(AnimalController::getCacheKey()),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
