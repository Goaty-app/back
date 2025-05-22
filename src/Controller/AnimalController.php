<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\Herd;
use App\Repository\AnimalRepository;
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

#[Route('api', name: 'api_animal_')]
final class AnimalController extends AbstractController
{
    #[Route('/v1/herd/{herd}/animals', name: 'get_all_by_herd', methods: ['GET'])]
    public function getAllByHerd(
        Herd $herd,
        AnimalRepository $animalRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $cacheKey = 'api_animal_'.$herd->getId();
        $jsonData = $cache->get($cacheKey, function (ItemInterface $item) use ($animalRepository, $herd, $serializer) {
            $item->tag('animalsCache');
            $animals = $animalRepository->findBy(['herd' => $herd]);

            return $serializer->serialize($animals, 'json', ['groups' => ['animal']]);
        });

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/herd/{herd}/animals', name: 'create_in_herd', methods: ['POST'])]
    public function createInHerd(
        Herd $herd,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UrlGeneratorInterface $urlGenerator,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $animal = $serializer->deserialize($request->getContent(), Animal::class, 'json');

        $animal->setHerd($herd);

        $errors = $validator->validate($animal);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($animal);
        $entityManager->flush();

        $cache->invalidateTags(['animalsCache']);

        $jsonData = $serializer->serialize($animal, 'json', ['groups' => ['animal']]);
        $location = $urlGenerator->generate('api_animal_get', ['animal' => $animal->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/animals', name: 'get_all', methods: ['GET'])]
    public function getAll(
        AnimalRepository $animalRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $jsonData = $cache->get('allAnimals', function (ItemInterface $item) use ($animalRepository, $serializer) {
            $item->tag('animalsCache');
            $animals = $animalRepository->findAll();

            return $serializer->serialize($animals, 'json', ['groups' => ['animal']]);
        });

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animals/{animal}', name: 'get', methods: ['GET'])]
    public function get(
        Animal $animal,
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($animal, 'json', ['groups' => ['animal']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animals/{animal}', name: 'update', methods: ['PATCH'])]
    public function update(
        Animal $animal,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $animal = $serializer->deserialize(
            $request->getContent(),
            Animal::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $animal],
        );

        $errors = $validator->validate($animal);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($animal);
        $entityManager->flush();

        $cache->invalidateTags(['animalsCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/animals/{animal}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Animal $animal,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $entityManager->remove($animal);
        $entityManager->flush();

        $cache->invalidateTags(['animalsCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
