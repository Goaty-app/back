<?php

namespace App\Repository\Trait;

use App\Entity\User;

trait OwnedEntityRepositoryTrait
{
    public function findByOwner(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.owner = :owner')
            ->setParameter('owner', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByIdAndOwner(int $id, User $user): ?object
    {
        return $this->createQueryBuilder('e')
            ->where('e.id = :id')
            ->andWhere('e.owner = :owner')
            ->setParameter('id', $id)
            ->setParameter('owner', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
