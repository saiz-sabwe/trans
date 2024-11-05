<?php

namespace App\Service;

use App\Entity\Parking;
use App\Makuta\MakutaEndpointService;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

class ParkingService
{

    private EntityManagerInterface $entityManager;
    private Security $security;
    private LoggerInterface $logger;
    private ParameterBagInterface $params;
    private ArrayService $arrayService;
    private SubscriptionPricingService $subscriptionPricingService;
    private EnginService $enginService;
    private MakutaEndpointService $makutaEndpointService;
    private SubscriptionRepository $subscriptionRepository;
    private UserService $userService;


    public function __construct(
        EntityManagerInterface     $entityManager,
        Security                   $security,
        LoggerInterface            $logger,
        ParameterBagInterface      $params,
        ArrayService               $arrayService,
        SubscriptionPricingService $subscriptionPricingService,
        EnginService               $enginService,
        MakutaEndpointService      $makutaEndpointService,
        SubscriptionRepository     $subscriptionRepository,
        UserService $userService
    )

    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->logger = $logger;
        $this->params = $params;
        $this->arrayService = $arrayService;
        $this->subscriptionPricingService = $subscriptionPricingService;
        $this->enginService = $enginService;
        $this->makutaEndpointService = $makutaEndpointService;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userService = $userService;
    }

    public function findParkingByOneLabel(string $label) : Parking
    {

        $this->logger->info("# ParkingService > findParkingByOneLabel: start");
        $this->logger->info("# ParkingService > findParkingByOneLabel: data received", ['label' => $label]);

        $parking = $this->entityManager->getRepository(Parking::class)->findOneBy([
            'label' => $label
        ]);

        if(!($parking instanceof Parking))
        {
            $this->logger->info("# ParkingService > findParkingByOneLabel: parking non trouvé", ['label' => $label]);
            throw new \RuntimeException("parking non trouvé", Response::HTTP_BAD_REQUEST);
        }

        return $parking;

    }

}