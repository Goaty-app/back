<?php

namespace App\Service;

use App\Dto\CreateFoodStockDto;
use App\Dto\UpdateFoodStockDto;
use App\Entity\FoodStock;
use App\Entity\FoodStockType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FoodStockService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
    ) {
    }

    public function updateFoodStockType(
        FoodStock $foodStock,
        CreateFoodStockDto|UpdateFoodStockDto $foodStockDto,
        UserInterface $currentUser,
    ): void {
        if (!$foodStockDto->foodStockTypeId) {
            return;
        }

        /** @var FoodStockType */
        $foodStockType = $this->entityManager->getRepository(FoodStockType::class)->findOneByIdAndOwner($foodStockDto->foodStockTypeId, $currentUser);

        if (!$foodStockType) {
            throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
        }

        if ($foodStockType->getOwner() !== $currentUser) {
            throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
        }

        $foodStock->setFoodStockType($foodStockType);
    }
}
