<?php

namespace App\Repository;

use App\Entity\Engin;
use App\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscription>
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    /**
     * Retourne la liste des abonnements liés à un Engin donné.
     *
     * @param Engin $engin
     * @return Subscription[]
     */
    public function findByEngin(Engin $engin): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.engin = :engin')
            ->setParameter('engin', $engin)
            ->getQuery()
            ->getResult();
    }

    public function findOneLastByRegistration(string $registration)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->leftJoin('s.engin', 'e');

        $qb
            ->where('e.registration = :registration')
            ->setParameter('registration', $registration);

        $qb->orderBy('s.dateCreated', 'DESC');

        return $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }


    //    /**
    //     * @return Subscription[] Returns an array of Subscription objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Subscription
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
