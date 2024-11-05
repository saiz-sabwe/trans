<?php

namespace App\Controller;

use App\Entity\SubscriptionCategory;
use App\Entity\SubscriptionPricing;
use App\Service\ExceptionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{


    private ExceptionService $exceptionService;

    private LoggerInterface $logger;

    private EntityManagerInterface $entityManager;

    public function __construct(ExceptionService $exceptionService,LoggerInterface $logger,EntityManagerInterface $entityManager)
    {

        $this->exceptionService = $exceptionService;
        $this->logger = $logger;
        $this->entityManager = $entityManager;

    }

    #[Route('/detail/{label}/{apiKey}', name: 'app_detail')]
    public function insertSubscriptionDetail(string $label, string $apiKey): JsonResponse
    {
        // Créer une nouvelle catégorie d'abonnement avec les valeurs dynamiques
        $subscriptionCategory = new SubscriptionCategory();
        $subscriptionCategory->setLabel($label);
        $subscriptionCategory->setApiKey($apiKey);

        // Persister dans la base de données
        $this->entityManager->persist($subscriptionCategory);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Subscription category created successfully!',
            'label' => $label,
            'apiKey' => $apiKey,
        ]);
    }

    #[Route('/pricing/{label}/{minDay}/{maxDay}/{amount}', name: 'app_pricing')]
    public function insertSubscriptionPricing(string $label, int $minDay, int $maxDay, float $amount): JsonResponse
    {
        // Rechercher une SubscriptionCategory avec le label passé en paramètre
        $subscriptionCategory = $this->entityManager->getRepository(SubscriptionCategory::class)->findOneBy(['label' => $label]);

        if (!$subscriptionCategory) {
            return $this->json([
                'message' => 'Subscription Category not found',
            ], 404);
        }

        // Créer une nouvelle instance de SubscriptionPricing
        $subscriptionPricing = new SubscriptionPricing();
        $subscriptionPricing->setSubscriptionCategoy($subscriptionCategory);
        $subscriptionPricing->setMinDay($minDay);
        $subscriptionPricing->setMaxDay($maxDay);
        $subscriptionPricing->setAmount($amount);
        $subscriptionPricing->setDateCreated(new \DateTime());

        // Persister les données
        $this->entityManager->persist($subscriptionPricing);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Subscription Pricing created successfully!',
            'pricingId' => $subscriptionPricing->getId(),
        ]);
    }


}
