<?php

namespace App\Controller;

use App\Service\ExceptionService;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiNotificationController extends AbstractController
{
    private ExceptionService $exceptionService;
    private NotificationService $notificationService;

    public function __construct(ExceptionService $exceptionService, NotificationService $notificationService)
    {
        $this->exceptionService = $exceptionService;
        $this->notificationService = $notificationService;
    }

    #[Route('/api/notification/send-otp', name: 'app_api_notification_send_otp', methods: ['POST'])]
    public function apiSendOtp(Request $request): JsonResponse
    {
        try {
            $this->notificationService->sendOtp($request->toArray());
            return $this->json(['status' => 'OK',]);
        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }
}
