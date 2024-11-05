<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Agent>
 */
class AgentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agent::class);
    }

    //    /**
    //     * @return Agent[] Returns an array of Agent objects
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

    //    public function findOneBySomeField($value): ?Agent
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByCompany(Company $company)
    {
        $qb = $this->createQueryBuilder('a');

        $qb->leftJoin('a.company', 'c');

        $qb
            ->where('a.isDeleted = :isDeletedAgent')
            ->setParameter('isDeletedAgent', false);

        $qb
            ->andWhere('c.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false);

        $qb
            ->andWhere('a.company = :company')
            ->setParameter('company', $company->getId(), 'uuid');

        return $qb;
    }
}