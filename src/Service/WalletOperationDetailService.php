<?php

namespace App\Service;

use App\Entity\WalletOperationDetail;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class WalletOperationDetailService
{
    private LoggerInterface $logger;
    private EntityManagerInterface $em;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->em = $em;
    }

    public function findByMakutaId(string $makutaId, bool $throw = true)
    {
        $this->logger->info("# WalletOperationDetailService > findByMakutaId: Start", ['makutaId' => $makutaId]);

        $wod = $this->em->getRepository(WalletOperationDetail::class)->findOneBy(['makutaId' => $makutaId]);

        if(!($wod instanceof WalletOperationDetail) && $throw)
        {
            $this->logger->info("# WalletOperationDetailService > findByMakutaId: WalletOperationDetail aucune occurence trouvée", ['makutaId' => $makutaId]);
            throw new \RuntimeException("Aucun WalletOperationDetail trouvé", Response::HTTP_NOT_FOUND);
        }

        return $wod;
    }
}