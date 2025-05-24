<?php

namespace App\Service;

use App\Entity\Healthcare;
use App\Entity\HealthcareType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class HealthcareService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updateHealthcareType(Healthcare $healthcare, Request $request, UserInterface $currentUser): void
    {
        $requestData = json_decode($request->getContent(), true);
        $healthcareTypeId = $requestData['healthcareTypeId'] ?? null;

        if (!$healthcareTypeId) {
            return;
        }

        /** @var HealthcareType */
        $healthcareType = $this->entityManager->getRepository(HealthcareType::class)->findOneByIdAndOwner($healthcareTypeId, $currentUser);

        if (!$healthcareType) {
            throw new NotFoundHttpException();
        }

        if ($healthcareType->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $healthcare->setHealthcareType($healthcareType);
    }
}
