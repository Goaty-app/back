<?php

namespace App\Service;

use App\Entity\Herd;
use App\Entity\Production;
use App\Entity\ProductionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductionService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updateProductionType(Production $production, Request $request, UserInterface $currentUser): void
    {
        $requestData = json_decode($request->getContent(), true);
        $herdId = $requestData['production_type_id'] ?? null;

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

    public function updateHerd(Production $production, Request $request, UserInterface $currentUser): void
    {
        $requestData = json_decode($request->getContent(), true);
        $herdId = $requestData['herd_id'];

        if (!$herdId) {
            return;
        }

        /** @var Herd */
        $herd = $this->entityManager->getRepository(Herd::class)->findOneByIdAndOwner($herdId, $currentUser);

        if (!$herd) {
            throw new NotFoundHttpException();
        }

        if ($herd->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $production->setHerd($herd);
    }
}
