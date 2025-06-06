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
use Symfony\Contracts\Translation\TranslatorInterface;

class HerdService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
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
            throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
        }

        if ($herd->getOwner() !== $currentUser) {
            throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
        }

        $HerdAwareInterface->setHerd($herd);
    }
}
