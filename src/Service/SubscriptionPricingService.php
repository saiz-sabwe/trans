<?php

namespace App\Service;

use App\Entity\SubscriptionPricing;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionPricingService
{

    private LoggerInterface $logger;
    private EntityManagerInterface $em;
    private Security $security;
    private RsaService $rsaService;
    private ParameterBagInterface $bag;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, Security $security, RsaService $rsaService, ParameterBagInterface $bag)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->security = $security;
        $this->rsaService = $rsaService;
        $this->bag = $bag;
    }

    public function findOneBySubscriptionPricingTotalDay(int $totalDay): SubscriptionPricing
    {
        $this->logger->info("# SubscriptionPricingService > findOneBySubscriptionPricingTotalDay : Start");

        $subscriptionPricing = $this->em->getRepository(SubscriptionPricing::class)->createQueryBuilder('sp')
            ->where(':day BETWEEN sp.minDay AND sp.maxDay')
            ->setParameter('day', $totalDay)
            ->getQuery()
            ->getOneOrNullResult();


        if (!($subscriptionPricing instanceof SubscriptionPricing))
        {
            throw new \RuntimeException("tarification non trouvé", Response::HTTP_UNAUTHORIZED);
        }

        return $subscriptionPricing;
    }

}