<?php

namespace App\Service;

use App\Entity\Production;
use App\Entity\ProductionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductionService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function updateProductionType(Production $production, Request $request, UserInterface $currentUser): void
    {
        $requestData = json_decode($request->getContent(), true);
        $herdId = $requestData['productionTypeId'] ?? null;

        if (!$herdId) {
            return;
        }

        /** @var ProductionType */
        $productionType = $this->entityManager->getRepository(ProductionType::class)->findOneByIdAndOwner($herdId, $currentUser);

        if (!$productionType) {
            throw new NotFoundHttpException();
        }

        if ($productionType->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $production->setProductionType($productionType);
    }
}
