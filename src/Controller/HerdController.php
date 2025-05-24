<?php

namespace App\Controller;

use App\Entity\Herd;
use App\Repository\HerdRepository;
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

#[Route('api', name: 'api_herd_')]
final class HerdController extends AbstractCachedController
{
    public static function getCacheKey(): string
    {
        return 'herds';
    }

    #[Route('/v1/herd', name: 'get_all', methods: ['GET'])]
    public function getAll(
        HerdRepository $herdRepository,
    ): JsonResponse {
        $cacheReturn = $this->getAllCachedItems($herdRepository, ['groups' => ['herd']]);

        return new JsonResponse($cacheReturn, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}', name: 'get', methods: ['GET'])]
    public function get(
        Herd $herd,
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($herd, 'json', ['groups' => ['herd']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse {
        /** @var Herd */
        $herd = $serializer->deserialize($request->getContent(), Herd::class, 'json');

        $errors = $validator->validate($herd);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $herd->setOwner($this->getUser());
        $herd->setCreatedAt(new DateTimeImmutable());

        $entityManager->persist($herd);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(static::getCacheKey()),
        ]);

        $jsonData = $serializer->serialize($herd, 'json', ['groups' => ['herd']]);
        $location = $urlGenerator->generate('api_herd_get', ['herd' => $herd->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/herd/{herd}', name: 'update', methods: ['PATCH'])]
    public function update(
        Herd $herd,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse {
        /** @var Herd */
        $herd = $serializer->deserialize($request->getContent(), Herd::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $herd]);

        $errors = $validator->validate($herd);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($herd);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getUserTag(),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/herd/{herd}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Herd $herd,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $entityManager->remove($herd);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getUserTag(),
        ]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
