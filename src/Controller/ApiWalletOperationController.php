<?php

namespace App\Controller;

use App\Service\ExceptionService;
use App\Service\WalletOperationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ApiWalletOperationController extends AbstractController
{

    private ExceptionService $exceptionService;
    private WalletOperationService $walletOperationService;
    private LoggerInterface $logger;

    public function __construct(ExceptionService $exceptionService, WalletOperationService $walletOperationService,LoggerInterface $logger)
    {
        $this->exceptionService = $exceptionService;
        $this->walletOperationService = $walletOperationService;
        $this->logger = $logger;
    }

    #[Route('/api/wallet-operation/pay-trip', name: 'app_api_wallet_operation_pay_trip', methods: ['POST'])]
    #[IsGranted('ROLE_DRIVER', message: "L'agent connecté n'est pas un Chauffeur")]
    public function apiPayTrip(Request $request): JsonResponse
    {
        try {
            $this->walletOperationService->payTrip($request->toArray(),"payerNumber");

            return $this->json([
                'message' => 'Paiement réussi !'
            ]);
        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }

    #[Route('/api/wallet-operation/pay-trip-card', name: 'app_api_wallet_operation_pay_trip_card', methods: ['POST'])]
    #[IsGranted('ROLE_DRIVER', message: "L'agent connecté n'est pas un Chauffeur")]
    public function apiPayTripCard(Request $request): JsonResponse
    {
        try {
            $this->walletOperationService->payTrip($request->toArray(),"payerCard");

            return $this->json([
                'message' => 'Paiement réussi !'
            ]);
        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }

    #[Route('/api/wallet-operation/pay-trip-momo', name: 'app_api_wallet_operation_pay_trip_momo', methods: ['POST'])]
    #[IsGranted('ROLE_DRIVER', message: "L'agent connecté n'est pas un Chauffeur")]
    public function apiPayTripMomo(Request $request): JsonResponse
    {

        $data = $request->toArray();

        $amount = $data['amount'];
        $payerOperator = $data['payerOperator'];
        $payerCurrency = $data['payerCurrency'];
        $payerAccountNumber = $data['payerAccountNumber'];

        $this->logger->info('Récupération des paramètres de la requête pour apiPayTripMomo', [
            'amount' => $amount,
            'payerOperator' => $payerOperator,
            'payerCurrency' => $payerCurrency,
            'payerAccountNumber' => $payerAccountNumber,
        ]);

        try {

          //  $result = $this->subscriptionService->create($request->toArray());
            $result = $this->walletOperationService->createTopup($amount, $payerOperator, $payerCurrency, $payerAccountNumber,'Paiement avec mobile money',"momo");

            $postAction = $result["postAction"];
            $makutaId = $result["makutaId"];


            $this->logger->info("# ApiWalletOperationController > apiCreateSubscribe: result", ["postAction" => $postAction,"makutaId" => $makutaId]);

            return $this->json([
                'makutaId'=> $makutaId,
                'postAction' => $postAction,
            ]);

        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }

    #[Route('/api/wallet-operation/register', name: 'app_api_wallet-operation_register', methods: ['POST'])]
    public function apiRegister(Request $request): JsonResponse
    {
        $this->logger->info("# ApiWalletOperationController > apiRegister: start");

        try {

            $subscription = $this->walletOperationService->register($request->toArray());

            $this->logger->info("# ApiWalletOperationController > apiRegister: result", ["result" => $subscription]);

            return $this->json($subscription, Response::HTTP_OK, []);

        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }

    #[Route('/api/wallet-operation/lastest-activity', name: 'app_api_wallet-operation_lastest_activity', methods: ['GET'])]
    public function apiLastestActivity(Request $request): JsonResponse
    {
        $this->logger->info("# ApiWalletOperationController > apiLastestActivity: start");

        try {

            $result = $this->walletOperationService->getLatestOperation();

            $this->logger->info("# ApiWalletOperationController > apiLastestActivity: result", ["result" => $result]);

            return $this->json($result, Response::HTTP_OK);

        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }



}
