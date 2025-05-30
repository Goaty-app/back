<?php

namespace App\Service;

use App\Dto\CreateHealthcareDto;
use App\Dto\UpdateHealthcareDto;
use App\Entity\Healthcare;
use App\Entity\HealthcareType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class HealthcareService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function updateHealthcareType(
        Healthcare $healthcare,
        CreateHealthcareDto|UpdateHealthcareDto $healthcareDto,
        UserInterface $currentUser,
    ): void {
        if (!$healthcareDto->healthcareTypeId) {
            return;
        }

        /** @var HealthcareType */
        $healthcareType = $this->entityManager->getRepository(HealthcareType::class)->findOneByIdAndOwner($healthcareDto->healthcareTypeId, $currentUser);

        if (!$healthcareType) {
            throw new NotFoundHttpException();
        }

        if ($healthcareType->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $healthcare->setHealthcareType($healthcareType);
    }
}
