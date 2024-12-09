<?php

namespace App\Controller;

use App\Makuta\MakutaService;
use App\Service\ExceptionService;
use App\Service\JWTDecoderService;
use App\Service\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ApiMakutaController extends AbstractController
{
    private LoggerInterface $logger;
    private ExceptionService $exceptionService;
    private MakutaService $makutaService;
    private JWTDecoderService $jwtDecoderService;
    private UserService $userService;

    public function __construct(LoggerInterface $logger, ExceptionService $exceptionService, MakutaService $makutaService, JWTDecoderService $jwtDecoderService, UserService $userService)
    {
        $this->logger = $logger;
        $this->exceptionService = $exceptionService;
        $this->makutaService = $makutaService;
        $this->jwtDecoderService = $jwtDecoderService;
        $this->userService = $userService;
    }

//    #[Route('/api/makuta/callback-result', name: 'app_api_makuta_callback_result')]
    #[Route('/callback/drc/makuta/ctob-callback-result', name: 'app_api_makuta_callback_result')]
    public function apiCallbackResult(Request $request): JsonResponse
    {
        try {
            $this->logger->info("# ApiMakutaController > apiCallbackResult: Start");

            // Get the Authorization header
            $bearerToken = $request->headers->get('Authorization');

            // Extract the username from the JWT token
            $username = $this->jwtDecoderService->getUsernameFromBearerToken($bearerToken);

            $this->logger->info("# ApiMakutaController > username : data received",["username"=>$username]);

            //TODO: a refractorer

            $user = $this->userService->findByUsername($username);
            $this->logger->info("# ApiMakutaController > $user : data received",["user"=>$user]);



            $this->makutaService->callbackResult($request->toArray(),$user->getId());

            return $this->json([
                'message' => 'OK'
            ]);
        } catch (\Exception $e) {
            $this->logger->info("# ApiMakutaController > apiCallbackResult > Exception: Start");
            $exception = $this->exceptionService->getException($e);

            $this->logger->info("# ApiMakutaController > apiCallbackResult > Exception", ['message' => $exception['message'], 'code' => $exception['code']]);

            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/api/makuta/t2p-callback', name: 'app_api_makuta_t2p_callback')]
    public function apiT2PCallback(Request $request): JsonResponse
    {
        try {
            $this->logger->info("# ApiMakutaController > apiT2PCallback: Start");

            $this->makutaService->callbackT2PResult($request->toArray());

            return $this->json(
                ['message' => "réussi"],
                Response::HTTP_OK
            );

        }catch (\Exception $e) {
            $this->logger->info("# ApiMakutaController > apiT2PCallback > Exception: Start");
            $exception = $this->exceptionService->getException($e);

            $this->logger->info("# ApiMakutaController > apiT2PCallback > Exception", ['message' => $exception['message'], 'code' => $exception['code']]);

            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/api/makuta/financial-corporation', name: 'app_api_makuta_financial_corporation')]
    public function apiMakutaOperator() :JsonResponse
    {
        $this->logger->info("# ApiMakutaController > apiMakutaOperator : Start");

        $data = $this->makutaService->makutaOperator();


        return $this->json(
            $data,
            Response::HTTP_OK
        );
    }
}
