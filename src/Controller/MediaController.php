<?php

namespace App\Controller;

use App\Controller\Abstract\AbstractCachedController;
use App\Entity\Healthcare;
use App\Entity\Media;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MediaController extends AbstractCachedController
{
    public function __construct(
        protected readonly TagAwareCacheInterface $cache,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/', name: 'app_media')]
    public function index(): JsonResponse
    {
        throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
    }

    #[Route('/api/v1/media/{media}', name: 'api_get_media', methods: ['GET'])]
    public function get(
        Media $media,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        $location = $urlGenerator->generate('app_media', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $location = $location.str_replace('/public/', '', $media->getPublicPath()).'/'.$media->getRealPath();

        return $media ?
            new JsonResponse(
                $serializer->serialize($media, 'json', ['groups' => ['media']]),
                Response::HTTP_OK,
                [
                    'location' => $location,
                ],
                true,
            ) :
            new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/v1/healthcare/{healthcare}/media', name: 'api_healthcare_create_media', methods: ['POST'])]
    public function create(
        Healthcare $healthcare,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        $media = new Media();
        $file = $request->files->get('file');
        $media->setFile($file)
            ->setOwner($this->getUser())
            ->setRealName($file->getClientOriginalName())
            ->setPublicPath('uploads/media')
            ->setUploadedAt(new DateTime())
            ->setHealthcare($healthcare)
        ;

        $entityManager->persist($media);
        $entityManager->flush();

        $this->cache->invalidateTags([
            $this->getTag(HealthcareController::getCacheKey()),
        ]);

        $jsonFile = $serializer->serialize($media, 'json', ['groups' => ['media']]);
        $location = $urlGenerator->generate('api_get_media', ['media' => $media->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);

        return new JsonResponse($jsonFile, Response::HTTP_CREATED, [
            'location' => $location,
        ], true);
    }
}
