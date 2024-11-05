<?php

namespace App\Controller;

use App\Service\ExceptionService;
use App\Service\SubscriptionPricingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiSubscriptionPricingController extends AbstractController
{
    private ExceptionService $exceptionService;
    private SubscriptionPricingService $subscriptionPricingService;

    public function __construct(ExceptionService $exceptionService, SubscriptionPricingService $subscriptionPricingService)
    {
        $this->exceptionService = $exceptionService;
        $this->subscriptionPricingService = $subscriptionPricingService;
    }

    #[Route('/api/subscription-pricing/pricing-by-day/{nbrOfDay}', name: 'app_api_subscription_pricing_pricing_by_day', methods: ['GET'])]
    public function apiPricingByDay(int $nbrOfDay): JsonResponse
    {
        try {

            $subscriptionPricing = $this->subscriptionPricingService->findOneBySubscriptionPricingTotalDay($nbrOfDay);

            return $this->json([$subscriptionPricing], Response::HTTP_OK, [], [
                'groups' => ['api']
            ]);
        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }
}
