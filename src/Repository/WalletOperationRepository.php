<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\WalletOperation;
use App\Service\UserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<WalletOperation>
 */
class WalletOperationRepository extends ServiceEntityRepository

{
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, WalletOperation::class);

        $this->logger = $logger;

    }


    public function findLastOperation(String $wallet_id): array
    {

//        $walletId = '0x0191C857A94375C2A5979E41EDCB7874';

        $this->logger->info("waalet id :",["wallet id"=>$wallet_id]);

        return $this->createQueryBuilder('wo')
            ->where('wo.wallet = :wallet_id')
            ->setParameter('wallet_id', $wallet_id, 'uuid')
            ->getQuery()
            ->getResult();

    }


//  public function findLastOperation(int $max): array
//    {
//        return $this->createQueryBuilder('wo')
//            ->innerJoin('wo.wallet', 'w') // Join with the wallet table
//            ->orderBy('wo.dateCreated', 'DESC')
//            ->setMaxResults($max)
//            ->getQuery()
//            ->getResult();
//    }



//    public function findLastOperation(int $max): array
//    {
//        return $this->createQueryBuilder('wo')
//            ->orderBy('wo.dateCreated', 'DESC')
//            ->setMaxResults($max)
//            ->getQuery()
//            ->getResult();
//    }


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
