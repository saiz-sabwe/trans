<?php

namespace App\Makuta;

use App\Service\ArrayService;
use App\Service\WalletOperationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class MakutaService
{
    private LoggerInterface $logger;
    private ArrayService $arrayService;
    private WalletOperationService $walletOperationService;
    private MakutaEndpointService $makutaEndpointService;

    public function __construct(LoggerInterface $logger, ArrayService $arrayService, WalletOperationService $walletOperationService, MakutaEndpointService $makutaEndpointService)
    {
        $this->logger = $logger;
        $this->arrayService = $arrayService;
        $this->walletOperationService = $walletOperationService;
        $this->makutaEndpointService = $makutaEndpointService;
    }

    public function callbackResult(array $data): void
    {
        $this->logger->info("# MakutaService > callbackResult: Start", ['dataReceived' => $data]);

        //region Vérification des champs obligatoires venant de Makuta Callback Result
        $structure = [
            'code',
            'message',
            'contents'
        ];

        $this->arrayService->notInArray($structure, $data);

        $dataContent = $data['contents'];

        $structure = [
            'financialTransaction'
        ];

        $this->arrayService->notInArray($structure, $dataContent);
        //endregion

        $ft = $dataContent['financialTransaction'];
        $code = $data['code'];
        $id =  $dataContent['user'];



        $this->walletOperationService->closeTopup($ft, $code, $id);

//        //declencher la notification oneSignal
//        $id = "0191a900-1d23-75b7-95de-4a6f96705a75";
//        $notification = $this->oneSignalService->sendPushNotification($data,$id);

        $this->logger->info("# MakutaService > callbackResult: end Successfully", ['dataReceived' => $data]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function callbackT2PResult(array $data): void
    {
        $this->logger->info("# MakutaService > callbackT2PResult : Start", ['dataReceived' => $data]);

        //region Vérification des champs obligatoires venant du Tap To Phone
        $structure = [
            'status',
            'visaReference',
            'transactionId',
            'message',
            'signature',
            'operationType',
        ];

        $this->arrayService->array_diff($structure, $data);
        //endregion

        //region Status
        $status = trim($data['status']) ?: null;

        if ($status === null)
        {
            $this->logger->info("# MakutaService > callbackT2PResult : Le status est null", ['status' => $data['status']]);
            throw new \RuntimeException("Status invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region VisaReference
        $visaReference = trim($data['visaReference']) ?: null;

//        if ($visaReference === null)
//        {
//            $this->logger->info("# MakutaService > callbackT2PResult : visaReference est null", ['visaReference' => $data['visaReference']]);
//            throw new \RuntimeException("la reference de visa est invalide", Response::HTTP_UNAUTHORIZED);
//        }
        //endregion

        //region TransactionId
        $transactionId = trim($data['transactionId']) ?: null;

        if($transactionId === null)
        {
            $this->logger->info("# MakutaService > callbackT2PResult : transactionId est null", ['transactionId' => $data['transactionId']]);
            throw new \RuntimeException("L'identifiant de la transaction est invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region Message
        $message = trim($data['message']) ?: null;

        if ($message === null)
        {
            $this->logger->info("# MakutaService > callbackT2PResult : Le message est null", ['message' => $data['message']]);
            throw new \RuntimeException("Le message est invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region Signature
        $signature = trim($data['signature']) ?: null;

        if ($signature === null)
        {
            $this->logger->info("# MakutaService > callbackT2PResult : La signature est null", ['signature' => $data['signature']]);
            throw new \RuntimeException("La signature est invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        //region OperationType
        $operationType = trim($data['operationType']) ?: null;

        if ($operationType === null)
        {
            $this->logger->info("# MakutaService > callbackT2PResult : operationType est null", ['operationType' => $data['operationType']]);
            throw new \RuntimeException("Le type d'opération est invalide invalide", Response::HTTP_UNAUTHORIZED);
        }
        //endregion

        $data = $this->makutaEndpointService->visaT2PCallback(
            $status,
            $visaReference,
            $transactionId,
            $message,
            $signature,
            $operationType
        );

        if (empty($data)){
            $this->logger->info("# MakutaService > callbackT2PResult : data est null", ['data' => $data]);
            throw new \RuntimeException("Données invalide", Response::HTTP_UNAUTHORIZED);
        }

        //region Vérification des champs obligatoires venant de Makuta Callback T2P
        $structure = [
            'message',
        ];

        $this->arrayService->array_diff($structure, $data);

        $dataContent = $data['contents'];

        $structure = [
            'financialTransaction'
        ];

        $this->arrayService->notInArray($structure, $dataContent);
        //endregion

        $ft = $transactionId;
        $code = $data['code'];

        $this->walletOperationService->closeTopup($ft, $code);

        $this->logger->info("# MakutaService > callbackT2PResult : End Success", ['data' => $data]);

    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function makutaOperator(): array
    {
        $this->logger->info("# MakutaService > makutaOperator : Start");

        $data = $this->makutaEndpointService->makutaOperator();

        if(empty($data)){
            $this->logger->info("# MakutaService > makutaOperator : data est null", ['data' => $data]);
            throw new \RuntimeException("Données invalide", Response::HTTP_UNAUTHORIZED);
        }

        return $data;
    }
}