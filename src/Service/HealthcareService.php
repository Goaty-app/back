<?php

namespace App\Service;

use App\Dto\CreateHealthcareDto;
use App\Dto\UpdateHealthcareDto;
use App\Entity\Healthcare;
use App\Entity\HealthcareType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class HealthcareService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
    ) {
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
            throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
        }

        if ($healthcareType->getOwner() !== $currentUser) {
            throw new NotFoundHttpException($this->translator->trans('exception.not_found'));
        }

        $healthcare->setHealthcareType($healthcareType);
    }
}
