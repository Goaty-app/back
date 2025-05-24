<?php

namespace App\Service;

use App\Entity\Animal;
use App\Entity\AnimalType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class AnimalService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updateAnimalType(Animal $animal, Request $request, UserInterface $currentUser): void
    {
        $requestData = json_decode($request->getContent(), true);
        $animalTypeId = $requestData['animalTypeId'] ?? null;

        if (!$animalTypeId) {
            return;
        }

        /** @var AnimalType */
        $animalType = $this->entityManager->getRepository(AnimalType::class)->findOneByIdAndOwner($animalTypeId, $currentUser);

        if (!$animalType) {
            throw new NotFoundHttpException();
        }

        if ($animalType->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $animal->setAnimalType($animalType);
    }
}
