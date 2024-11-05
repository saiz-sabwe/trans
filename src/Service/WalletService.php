<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;

class WalletService
{
    private EntityManagerInterface $entityManager;

    // Constructor to inject services
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function createWallet(User $user, bool $isPersist = false): Wallet
    {

        $wallet = new Wallet();

        $wallet->setOwner($user);
        $wallet->setBalance(0.0);

        if($isPersist)
        {
            $this->entityManager->persist($wallet);
            $this->entityManager->flush();
        }


        return $wallet;
    }

    public function getBalance(User $user): float
    {
        $wallet = $user->getWallet();
        return $wallet instanceof Wallet ? $wallet->getBalance() : 0.00;
    }

}