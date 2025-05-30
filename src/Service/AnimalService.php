<?php

namespace App\Service;

use App\Dto\CreateAnimalDto;
use App\Dto\UpdateAnimalDto;
use App\Entity\Animal;
use App\Entity\AnimalType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class AnimalService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function updateAnimalType(
        Animal $animal,
        CreateAnimalDto|UpdateAnimalDto $animalDto,
        UserInterface $currentUser,
    ): void {
        if (!$animalDto->animalTypeId) {
            return;
        }

        /** @var AnimalType */
        $animalType = $this->entityManager->getRepository(AnimalType::class)->findOneByIdAndOwner($animalDto->animalTypeId, $currentUser);

        if (!$animalType) {
            throw new NotFoundHttpException();
        }

        if ($animalType->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $animal->setAnimalType($animalType);
    }
}
