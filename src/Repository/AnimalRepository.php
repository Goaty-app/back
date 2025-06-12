<?php

namespace App\Repository;

use App\Contract\OwnerScopedRepositoryInterface;
use App\Entity\Animal;
use App\Repository\Trait\FindsByOwnerRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Animal>
 */
class AnimalRepository extends ServiceEntityRepository implements OwnerScopedRepositoryInterface
{
    use FindsByOwnerRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Animal::class);
    }

    public function getStatsByGroup(string $groupBy, UserInterface $user): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id) as count')
            ->where('a.owner = :user')
            ->setParameter('user', $user)
        ;

        $fieldMapping = [
            'originCountry' => 'a.originCountry',
            'gender'        => 'a.gender',
        ];

        $groupField = $fieldMapping[$groupBy];

        $qb->addSelect("{$groupField} as value")
            ->groupBy($groupField)
            ->orderBy('count', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Animal[] Returns an array of Animal objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Animal
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
