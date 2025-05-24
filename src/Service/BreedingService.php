<?php

namespace App\Service;

use App\Entity\Animal;
use App\Entity\Breeding;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class BreedingService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function updateFemale(Breeding $breeding, Request $request, UserInterface $currentUser): void
    {
        $requestData = json_decode($request->getContent(), true);
        $femaleId = $requestData['femaleId'] ?? null;

        if (!$femaleId) {
            return;
        }

        /** @var Animal */
        $animal = $this->entityManager->getRepository(Animal::class)->findOneByIdAndOwner($femaleId, $currentUser);

        if (!$animal) {
            throw new NotFoundHttpException();
        }

        if ($animal->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $breeding->setFemale($animal);
    }

    public function updateMale(Breeding $breeding, Request $request, UserInterface $currentUser): void
    {
        $requestData = json_decode($request->getContent(), true);
        $maleId = $requestData['maleId'] ?? null;

        if (!$maleId) {
            return;
        }

        /** @var Animal */
        $animal = $this->entityManager->getRepository(Animal::class)->findOneByIdAndOwner($maleId, $currentUser);

        if (!$animal) {
            throw new NotFoundHttpException();
        }

        if ($animal->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $breeding->setMale($animal);
    }
}
