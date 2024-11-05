<?php

namespace App\Controller;

use App\Makuta\MakutaService;
use App\Service\ExceptionService;
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

    public function __construct(LoggerInterface $logger, ExceptionService $exceptionService, MakutaService $makutaService)
    {
        $this->logger = $logger;
        $this->exceptionService = $exceptionService;
        $this->makutaService = $makutaService;
    }

    #[Route('/api/makuta/callback-result', name: 'app_api_makuta_callback_result')]
    public function apiCallbackResult(Request $request): JsonResponse
    {
        try {
            $this->logger->info("# ApiMakutaController > apiCallbackResult: Start");

            $this->makutaService->callbackResult($request->toArray());

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
}
