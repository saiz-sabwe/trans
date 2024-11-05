<?php

namespace App\Controller;

use App\Service\ExceptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiWalletController extends AbstractController
{
    private ExceptionService $exceptionService;

    public function __construct(ExceptionService $exceptionService)
    {
        $this->exceptionService = $exceptionService;
    }

    #[Route('/api/wallet/my-balances', name: 'app_api_wallet_my_balance')]
    public function apiMyBalance(Request $request): JsonResponse
    {
        try {
            $wallets = $this->getUser()->getWallets();

            return $this->json($wallets, Response::HTTP_OK, [], [
                'groups' => ['api']
            ]);
        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }
}
