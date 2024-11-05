<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Engin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Engin>
 */
class EnginRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Engin::class);
    }

    //    /**
    //     * @return Engin[] Returns an array of Engin objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Engin
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByCompany(Company $company)
    {
        $qb = $this->createQueryBuilder('e');

        $qb->leftJoin('e.company', 'c');

        $qb
            ->where('e.isWorking = :isWorking')
            ->setParameter('isWorking', true);

        $qb
            ->andWhere('c.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false);

        $qb
            ->andWhere('e.company = :company')
            ->setParameter('company', $company->getId(), 'uuid');

        return $qb;
    }
}
