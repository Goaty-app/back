<?php

namespace App\Service;

use App\Dto\CreateFoodStockDto;
use App\Dto\UpdateFoodStockDto;
use App\Entity\FoodStock;
use App\Entity\FoodStockType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class FoodStockService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
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
            throw new NotFoundHttpException();
        }

        if ($foodStockType->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $foodStock->setFoodStockType($foodStockType);
    }
}
