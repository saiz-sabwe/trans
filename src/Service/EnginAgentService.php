<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\EnginAgent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class EnginAgentService
{
    private LoggerInterface $logger;
    private EntityManagerInterface $em;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->em = $em;
    }

    public function findOneLastByAgent(Agent $agent, bool $throw = true)
    {
        $enginAgent = $this->em->getRepository(EnginAgent::class)->findOneBy(['agent' => $agent], ['dateCreated' => 'DESC']);

        if(!($enginAgent instanceof EnginAgent) && $throw)
        {
            throw new \RuntimeException("Chauffeur non affecté un véhicule", Response::HTTP_NOT_FOUND);
        }

        return $enginAgent;
    }
}