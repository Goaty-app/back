<?php

namespace App\Service;

use App\Dto\CreateProductionDto;
use App\Dto\UpdateProductionDto;
use App\Entity\Production;
use App\Entity\ProductionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function updateProductionType(
        Production $production,
        CreateProductionDto|UpdateProductionDto $productionDto,
        UserInterface $currentUser,
    ): void {
        if (!$productionDto->productionTypeId) {
            return;
        }

        /** @var ProductionType */
        $productionType = $this->entityManager->getRepository(ProductionType::class)->findOneByIdAndOwner($productionDto->productionTypeId, $currentUser);

        if (!$productionType) {
            throw new NotFoundHttpException();
        }

        if ($productionType->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $production->setProductionType($productionType);
    }
}
