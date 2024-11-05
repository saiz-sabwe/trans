<?php

namespace App\Repository;

use App\Entity\WalletOperation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WalletOperation>
 */
class WalletOperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WalletOperation::class);
    }

    /**
     * Récupère les 10 dernières opérations de portefeuille
     *
     * @return WalletOperation[]
     */
    public function findLastOperation(int $max): array
    {
        return $this->createQueryBuilder('wo')
            ->orderBy('wo.dateCreated', 'DESC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?WalletOperation
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
