<?php

namespace App\Service;

use App\Entity\Animal;
use App\Entity\Birth;
use App\Entity\Breeding;
use App\Repository\BirthRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class BirthService
{
    public function __construct(private EntityManagerInterface $entityManager, private BirthRepository $birthRepository)
    {
    }

    public function updateBreeding(Birth $birth, Request $request, UserInterface $currentUser): void
    {
        $requestData = json_decode($request->getContent(), true);
        $breedingId = $requestData['breedingId'] ?? null;

        if (!$breedingId) {
            return;
        }

        /** @var Breeding */
        $breeding = $this->entityManager->getRepository(Breeding::class)->findOneByIdAndOwner($breedingId, $currentUser);

        if (!$breeding) {
            throw new NotFoundHttpException();
        }

        if ($breeding->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $birth->setBreeding($breeding);
    }

    public function updateChild(Birth $birth, Request $request, UserInterface $currentUser): void
    {
        $requestData = json_decode($request->getContent(), true);
        $childId = $requestData['childId'] ?? null;

        if (!$childId) {
            return;
        }

        /** @var Animal */
        $animal = $this->entityManager->getRepository(Animal::class)->findOneByIdAndOwner($childId, $currentUser);

        // Hack to make a ManyToOne like a OneToOne
        if ($this->birthRepository->findOneByChildExcludingId($animal, $birth->getId())) {
            throw new ConflictHttpException(
                'animal already linked to a birth',
            );
        }

        if (!$animal) {
            throw new NotFoundHttpException();
        }

        if ($animal->getOwner() !== $currentUser) {
            throw new NotFoundHttpException();
        }

        $birth->setChild($animal);
    }
}
