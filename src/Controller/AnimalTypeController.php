<?php

namespace App\Controller;

use App\Entity\AnimalType;
use App\Repository\AnimalRepository;
use App\Repository\AnimalTypeRepository;
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

#[Route('api', name: 'api_animal_type_')]
final class AnimalTypeController extends AbstractController
{
    #[Route('/v1/animal-types', name: 'get_all', methods: ['GET'])]
    public function getAll(
        AnimalTypeRepository $typeRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $jsonData = $cache->get('allAnimalTypes', function (ItemInterface $item) use ($typeRepository, $serializer) {
            $item->tag('animalTypesCache');
            $types = $typeRepository->findAll();

            return $serializer->serialize($types, 'json', ['groups' => ['type']]);
        });

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animal-types', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UrlGeneratorInterface $urlGenerator,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $type = $serializer->deserialize($request->getContent(), AnimalType::class, 'json');
        $type->setOwner($this->getUser());
        $errors = $validator->validate($type);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($type);
        $em->flush();

        $cache->invalidateTags(['animalTypesCache']);

        $jsonData = $serializer->serialize($type, 'json', ['groups' => ['type']]);
        $location = $urlGenerator->generate('api_animal_type_get', ['id' => $type->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonData, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/v1/animal-types/{id}', name: 'get', methods: ['GET'])]
    public function get(
        AnimalType $type,
        SerializerInterface $serializer,
    ): JsonResponse {
        $jsonData = $serializer->serialize($type, 'json', ['groups' => ['type']]);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/v1/animal-types/{id}', name: 'update', methods: ['PATCH'])]
    public function update(
        AnimalType $type,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $serializer->deserialize(
            $request->getContent(),
            AnimalType::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $type],
        );

        $errors = $validator->validate($type);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->flush();
        $cache->invalidateTags(['animalTypesCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/animal-types/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        AnimalType $type,
        EntityManagerInterface $em,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $em->remove($type);
        $em->flush();

        $cache->invalidateTags(['animalTypesCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/animals/{animal_id}/types/{type_id}', name: 'associate', methods: ['POST'])]
    public function associateType(
        int $animal_id,
        int $type_id,
        AnimalRepository $animalRepo,
        AnimalTypeRepository $typeRepo,
        EntityManagerInterface $em,
    ): JsonResponse {
        $animal = $animalRepo->find($animal_id);
        $type = $typeRepo->find($type_id);

        if (!$animal || !$type) {
            return new JsonResponse(['error' => 'Animal or Type not found'], Response::HTTP_NOT_FOUND);
        }

        $animal->addType($type);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/v1/animals/{animal_id}/types/{type_id}', name: 'dissociate', methods: ['DELETE'])]
    public function removeType(
        int $animal_id,
        int $type_id,
        AnimalRepository $animalRepo,
        AnimalTypeRepository $typeRepo,
        EntityManagerInterface $em,
    ): JsonResponse {
        $animal = $animalRepo->find($animal_id);
        $type = $typeRepo->find($type_id);

        if (!$animal || !$type) {
            return new JsonResponse(['error' => 'Animal or Type not found'], Response::HTTP_NOT_FOUND);
        }

        $animal->removeType($type);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
