<?php

namespace App\Repository;

use App\Entity\WalletOperationDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WalletOperationDetail>
 */
class WalletOperationDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WalletOperationDetail::class);
    }

    /**
     * Trouve un WalletOperationDetail par son makutaId.
     *
     * @param string $makutaId
     * @return WalletOperationDetail|null
     */
    public function findByMakutaId(string $makutaId): ?WalletOperationDetail
    {
        return $this->createQueryBuilder('wod')
            ->where('wod.makutaId = :makutaId')
            ->setParameter('makutaId', $makutaId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return WalletOperationDetail[] Returns an array of WalletOperationDetail objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?WalletOperationDetail
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
