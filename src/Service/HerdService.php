<?php

namespace App\Service;

use App\Entity\Herd;
use App\Interface\HasHerd;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class HerdService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function updateHerd(HasHerd $hasHerd, Request $request, UserInterface $currentUser): void
    {
        $requestData = json_decode($request->getContent(), true);
        $herdId = $requestData['herdId'] ?? null;

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

        $hasHerd->setHerd($herd);
    }
}
