<?php

namespace App\Service;

use App\Dto\CreateBreedingDto;
use App\Dto\UpdateBreedingDto;
use App\Entity\Animal;
use App\Entity\Breeding;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BreedingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
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
            throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
        }

        if ($animal->getOwner() !== $currentUser) {
            throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
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
            throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
        }

        if ($animal->getOwner() !== $currentUser) {
            throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
        }

        $breeding->setMale($animal);
    }
}
