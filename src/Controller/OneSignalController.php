<?php

namespace App\Controller;

use App\OneSignal\OneSignalEndpointService;
use App\OneSignal\OneSignalService;
use App\Service\UserService;
use App\Service\WalletOperationService;
use App\Service\WalletService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class OneSignalController extends AbstractController
{

    private UserService $userService;
    private WalletOperationService $walletOperationService;
    private WalletService $walletService;
    private OneSignalService $oneSignalService;
    private LoggerInterface $logger;

    public function __construct(UserService $userService, WalletOperationService $walletOperationService, WalletService $walletService, OneSignalService $oneSignalService, LoggerInterface $logger)
    {
        $this->userService = $userService;
        $this->walletService = $walletService;
        $this->walletOperationService = $walletOperationService;
        $this->oneSignalService = $oneSignalService;
        $this->logger = $logger;
    }

    #[Route('/one/signal', name: 'app_one_signal')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/OneSignalController.php',
        ]);
    }

    #[Route('/onesignal/send/push', name: 'app_onesignal_send_push')]
    public function send(): JsonResponse
    {
        $this->logger->info("# OneSignalController > send : Start");

        $result = $this->oneSignalService->sendPushNotification();

        $this->logger->info("# OneSignalController > send : data received : ",["result"=>$result]);

        return $this->json([
            'result' => $result,
        ]);



    }
}
