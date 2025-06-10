<?php

namespace App\Service;

use App\Dto\CreateBreedingDto;
use App\Dto\UpdateBreedingDto;
use App\Entity\Animal;
use App\Entity\Breeding;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class BreedingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function updateFemale(
        Breeding $breeding,
        CreateBreedingDto|UpdateBreedingDto $breedingDto,
        UserInterface $currentUser,
    ): void {
        if (!$breedingDto->femaleId) {
            return;
        }

        /** @var Animal */
        $animal = $this->entityManager->getRepository(Animal::class)->findOneByIdAndOwner($breedingDto->femaleId, $currentUser);

        if (!$animal) {
            throw new NotFoundHttpException();
        }

        if ($animal->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $breeding->setFemale($animal);
    }

    public function updateMale(
        Breeding $breeding,
        CreateBreedingDto|UpdateBreedingDto $breedingDto,
        UserInterface $currentUser,
    ): void {
        if (!$breedingDto->maleId) {
            return;
        }

        /** @var Animal */
        $animal = $this->entityManager->getRepository(Animal::class)->findOneByIdAndOwner($breedingDto->maleId, $currentUser);

        if (!$animal) {
            throw new NotFoundHttpException();
        }

        if ($animal->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $breeding->setMale($animal);
    }
}
