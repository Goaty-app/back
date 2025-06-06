<?php

namespace App\Repository;

use App\Contract\OwnerScopedRepositoryInterface;
use App\Entity\Animal;
use App\Entity\Birth;
use App\Repository\Trait\FindsByOwnerRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Birth>
 */
class BirthRepository extends ServiceEntityRepository implements OwnerScopedRepositoryInterface
{
    use FindsByOwnerRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Birth::class);
    }

    // Hack to make a ManyToOne like a OneToOne
    public function findOneByChildExcludingId(Animal $child, ?int $excludedBirthId): ?Birth
    {
        $qb = $this->createQueryBuilder('b')
            ->andWhere('b.child = :child')
            ->setParameter('child', $child)
        ;

        if (null !== $excludedBirthId && $excludedBirthId > 0) {
            $qb->andWhere('b.id != :excludedId')
                ->setParameter('excludedId', $excludedBirthId)
            ;
        }

        return $qb->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    //    /**
    //     * @return Birth[] Returns an array of Birth objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Birth
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
