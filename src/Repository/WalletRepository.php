<?php

namespace App\Repository;

use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Wallet>
 */
class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    // Récupérer uniquement le solde d'un utilisateur à partir de son ID
    public function findBalanceByUserId(Uuid $userId): ?float
    {
        return $this->createQueryBuilder('w')
            ->select('w.balance')  // Sélectionner uniquement le champ balance
            ->andWhere('w.owner = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();  // Récupérer un seul résultat sous forme scalaire (float)
    }

    // Récupérer uniquement l'ID du portefeuille d'un utilisateur à partir de son ID
    public function findWalletIdByUserId(Uuid $userId): ?Uuid
    {
        return $this->createQueryBuilder('w')
            ->select('w.id')  // Sélectionner uniquement le champ id
            ->andWhere('w.owner = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();  // Récupérer un seul résultat sous forme scalaire (UUID)
    }

    // Récupérer l'objet Wallet entier d'un utilisateur à partir de son ID
    public function findWalletByUserId(Uuid $userId): ?Wallet
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.owner = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();  // Récupérer un seul résultat ou null
    }
}
