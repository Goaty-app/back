<?php

namespace App\Service;

use App\Dto\CreateAnimalDto;
use App\Dto\UpdateAnimalDto;
use App\Entity\Animal;
use App\Entity\AnimalType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AnimalService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
    ) {
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
            throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
        }

        if ($animalType->getOwner() !== $currentUser) {
            throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
        }

        $animal->setAnimalType($animalType);
    }
}
