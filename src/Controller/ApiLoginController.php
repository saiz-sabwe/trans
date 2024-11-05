<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ExceptionService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiLoginController extends AbstractController
{
    private ExceptionService $exceptionService;
    private LoggerInterface $logger;

    public function __construct(ExceptionService $exceptionService,LoggerInterface $logger)
    {
        $this->exceptionService = $exceptionService;
        $this->logger = $logger;
    }

    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function apiLoginCheck()
    {}

//    #[Route('/api/login', name: 'app_api_login', methods: ['POST'])]
//    public function apiLogin(#[CurrentUser] ?User $user): JsonResponse
//    {
//        try
//        {
//            $this->logger->info("# ApiLoginController > apiLogin: Start");
////            if (null === $user) {
////                //throw new \RuntimeException("Compte invalide", Response::HTTP_UNAUTHORIZED);
////                return $this->json(['message' => 'missing credentials',], Response::HTTP_UNAUTHORIZED);
////            }
//            $token = "xxxyyyzz";
//            return $this->json(['message' => 'User Connected !']);
//        } catch (\Exception $e)
//        {
//            $exception = $this->exceptionService->getException($e);
//            return $this->json(['message' => $exception['message']], $exception['code']);
//        }
//    }

//    #[Route('/api/v1/test', name: 'app_api_test')]
//    public function test(): Response
//    {
//        return $this->json([
//            'user' => "OK"
//        ]);
//    }
//
//    #[Route('/login/test', name: 'app_api_test_log')]
//    public function testLog(): Response
//    {
//        return $this->json([
//            'user' => "OK"
//        ]);
//    }
}
