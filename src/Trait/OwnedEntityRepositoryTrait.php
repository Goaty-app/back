<?php

namespace App\Trait;

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

    public function findByOwnerFlex(string $column, int $value, User $user): array
    {
        return $this->createQueryBuilder('e')
            ->where("e.{$column} = :value")
            ->andWhere('e.owner = :owner')
            ->setParameter('value', $value)
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
