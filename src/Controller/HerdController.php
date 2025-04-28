<?php

namespace App\Controller;

use App\Entity\Herd;
use App\Repository\HerdRepository;
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

#[Route('api', name: 'api_herd_')]
final class HerdController extends AbstractController
{
    #[Route('/v1/herd', name: 'get_all', methods: ['GET'])]
    public function getAll(
        HerdRepository $herdRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $cacheReturn = $cache->get('getAllHerds', function (ItemInterface $item) use ($herdRepository, $serializer) {
            $item->tag('herdsCache');
            $data = $herdRepository->findByOwner($this->getUser());
            $jsonData = $serializer->serialize($data, 'json', ['groups' => ['herd']]);

            return $jsonData;
        });

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
        TagAwareCacheInterface $cache,
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

        $cache->invalidateTags(['herdsCache']);

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
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        /** @var Herd */
        $herd = $serializer->deserialize($request->getContent(), Herd::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $herd]);

        $entityManager->persist($herd);
        $entityManager->flush();

        $cache->invalidateTags(['herdsCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/herd/{herd}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Herd $herd,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $entityManager->remove($herd);
        $entityManager->flush();

        $cache->invalidateTags(['herdsCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
