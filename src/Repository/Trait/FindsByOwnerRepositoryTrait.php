<?php

namespace App\Repository\Trait;

use App\Entity\User;

trait FindsByOwnerRepositoryTrait
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

    public function filterByOwnerFlex(string $column, string $value, User $user): array
    {
        return $this->createQueryBuilder('e')
            ->where("e.{$column} LIKE :value")
            ->andWhere('e.owner = :owner')
            ->setParameter('value', '%'.$value.'%')
            ->setParameter('owner', $user)
            ->getQuery()
            ->getResult()
        ;
    }
}
