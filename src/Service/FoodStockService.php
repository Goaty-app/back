<?php

namespace App\Service;

use App\Entity\FoodStock;
use App\Entity\FoodStockType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class FoodStockService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updateFoodStockType(FoodStock $foodStock, Request $request, UserInterface $currentUser): void
    {
        $requestData = json_decode($request->getContent(), true);
        $foodStockTypeId = $requestData['food_stock_type_id'];

        if (!$foodStockTypeId) {
            return;
        }

        /** @var FoodStockType */
        $foodStockType = $this->entityManager->getRepository(FoodStockType::class)->findOneByIdAndOwner($foodStockTypeId, $currentUser);

        if (!$foodStockType) {
            throw new NotFoundHttpException();
        }

        if ($foodStockType->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $foodStock->setFoodStockType($foodStockType);
    }
}
