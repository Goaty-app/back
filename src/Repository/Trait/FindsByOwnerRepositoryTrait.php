<?php

namespace App\Repository\Trait;

use App\Entity\User;
use App\Util\FilterItem;
use App\Util\FilterMapping;

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

    public function filterByOwnerFlex(FilterMapping $filterMapping, User $user): array
    {
        $qb = $this->createQueryBuilder('e');

        /** @var FilterItem $mapping */
        foreach ($filterMapping->get() as $mapping) {
            $qb->andWhere("e.{$mapping->getColumn()} {$mapping->getOperator()} :{$mapping->getColumn()}")
                ->setParameter($mapping->getColumn(), $mapping->getValue())
            ;
        }

        $qb->andWhere('e.owner = :owner')
            ->setParameter('owner', $user)
        ;

        return $qb->getQuery()->getResult();
    }
}
