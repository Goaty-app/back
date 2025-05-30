<?php

namespace App\Service;

use App\Contract\HerdAwareInterface;
use App\Dto\UpdateAnimalDto;
use App\Dto\UpdateFoodStockDto;
use App\Dto\UpdateProductionDto;
use App\Entity\Herd;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class HerdService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function updateHerd(
        HerdAwareInterface $HerdAwareInterface,
        UpdateAnimalDto|UpdateFoodStockDto|UpdateProductionDto $dto,
        UserInterface $currentUser,
    ): void {
        if (!$dto->herdId) {
            return;
        }

        /** @var Herd */
        $herd = $this->entityManager->getRepository(Herd::class)->findOneByIdAndOwner($dto->herdId, $currentUser);

        if (!$herd) {
            throw new NotFoundHttpException();
        }

        if ($herd->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $HerdAwareInterface->setHerd($herd);
    }
}
