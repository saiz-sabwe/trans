<?php

namespace App\Controller;

use App\Service\ExceptionService;
use App\Service\SubscriptionService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiSubscriptionController extends AbstractController
{

    private SubscriptionService $subscriptionService;
    private ExceptionService $exceptionService;
    private LoggerInterface $logger;

    public function __construct(SubscriptionService $subscriptionService, ExceptionService $exceptionService, LoggerInterface $logger)
    {
        $this->subscriptionService = $subscriptionService;
        $this->exceptionService = $exceptionService;
        $this->logger = $logger;

    }

    #[Route('/api/subscription/pay', name: 'app_api_subscription_pay', methods: ['POST'])]
    public function apiCreate(Request $request): JsonResponse
    {
        $this->logger->info("# ApiSubscriptionController > apiCreateSubscribe: start");

        try {

            $result = $this->subscriptionService->create($request->toArray(),"momo");

            $postAction = $result["postAction"];
            $makutaId = $result["makutaId"];

            $this->logger->info("# ApiSubscriptionController > apiCreateSubscribe: postAction", ["postAction" => $postAction]);

            return $this->json([
                'makutaId'=> $makutaId,
                'postAction' => $postAction,
            ]);

        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }

    #[Route('/api/subscription/check', name: 'app_api_subscription_pay_check', methods: ['POST'])]
    public function apiCheck(Request $request): JsonResponse
    {
        $this->logger->info("# ApiSubscriptionController > apiCheckSubscribe: Start");

        try {

            $result = $this->subscriptionService->checkSubscribe($request->toArray());

            $this->logger->info("# ApiSubscriptionController > apiCheckSubscribe: result", ["result" => $result]);

            return $this->json([$result]);

        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }


    #[Route('/api/subscription/detail', name: 'app_api_subscription_detail', methods: ['POST'])]
    public function apiDetail(Request $request): JsonResponse
    {
        $this->logger->info("# ApiSubscriptionController > apiDetailSubscribe: start");

        try {

            $result = $this->subscriptionService->detailSubscribe($request->toArray());

            $this->logger->info("# ApiSubscriptionController > apiDetailSubscribe: result", ["result" => $result]);

            return $this->json([
                'subscribe' => $result
            ]);

        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }

    #[Route('/api/wallet-operation/pay-trip-momo', name: 'app_api_wallet_operation_pay_trip_momo', methods: ['POST'])]

    #[Route('/api/subscription/register', name: 'app_api_subscription_register', methods: ['POST'])]
    public function apiRegister(Request $request): JsonResponse
    {
        $this->logger->info("# ApiSubscriptionController > apiRegister: start");

        try {

            $subscription = $this->subscriptionService->register($request->toArray());

            $this->logger->info("# ApiSubscriptionController > apiRegister: result", ["result" => $subscription]);

//            return $this->json(200);
            return $this->json($subscription, Response::HTTP_OK,[],['groups'=>['api']]);

        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }
}
