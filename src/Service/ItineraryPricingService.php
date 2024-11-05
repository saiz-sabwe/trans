<?php

namespace App\Service;

use App\Entity\Itinerary;
use App\Entity\ItineraryPricing;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class ItineraryPricingService
{
    private LoggerInterface $logger;
    private EntityManagerInterface $em;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->em = $em;
    }

    public function findOneLastByItinerary(Itinerary $itinerary, bool $throw = true)
    {
        $itineraryPricing = $this->em->getRepository(ItineraryPricing::class)->findOneBy(['itinerary' => $itinerary], ['dateCreated' => 'DESC']);

        if(!($itineraryPricing instanceof ItineraryPricing) && $throw)
        {
            throw new \RuntimeException("Vehicule sans trajet", Response::HTTP_NOT_FOUND);
        }

        return $itineraryPricing;
    }
}