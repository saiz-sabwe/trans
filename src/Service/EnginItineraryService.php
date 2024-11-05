<?php

namespace App\Service;

use App\Entity\Engin;
use App\Entity\EnginItinerary;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class EnginItineraryService
{
    private LoggerInterface $logger;
    private EntityManagerInterface $em;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->em = $em;
    }

    public function findOneLastByEngin(Engin $engin, bool $throw = true)
    {
        $enginItinerary = $this->em->getRepository(EnginItinerary::class)->findOneBy(['engin' => $engin], ['dateCreated' => 'DESC']);

        if(!($enginItinerary instanceof EnginItinerary) && $throw)
        {
            throw new \RuntimeException("Vehicule sans trajet", Response::HTTP_NOT_FOUND);
        }

        return $enginItinerary;
    }
}