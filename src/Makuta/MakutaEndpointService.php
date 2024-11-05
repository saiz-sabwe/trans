<?php

namespace App\Makuta;

use App\Entity\User;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MakutaEndpointService
{

    private LoggerInterface $logger;
    private HttpClientInterface $httpClient;
    private ParameterBagInterface $params;

    public function __construct(LoggerInterface $logger, HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->params = $params;
    }

    public function login(string $username, string $password): string
    {

        $this->logger->info("# MakutaService > login: Start");

        $userToken = "";

        // Préparer les headers et le body pour l'API de Makuta
        $url = $this->params->get('base.url') . '/auth/login/token';

        $headers = [
            'App-Token' => $this->params->get('app.token'),
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'username' => $username,
            'password' => $password,
        ];

        try {
            // Envoyer la requête POST vers Makuta
            $response = $this->httpClient->request('POST', $url, [
                'headers' => $headers,
                'json' => $payload,
            ]);

            // Traiter la réponse
            $statusCode = $response->getStatusCode();
            $content = $response->toArray();
            $headers = $response->getHeaders();


            if ($statusCode === Response::HTTP_OK) {
                // Connexion réussie
                $this->logger->info('# MakutaService < login : user connected successful');
                $userToken = $headers['user-token'][0];

            } else {
                // Gérer l'erreur de Makuta
                $this->logger->error('# MakutaService < login : user not connected');

                throw new \RuntimeException("connection failed", Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {

            // Gérer les erreurs réseau ou autres
            $this->logger->error('Error communicating with Makuta', ['exception' => $e->getMessage()]);
        }


        return $userToken;

    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    #[ArrayShape(["makutaId" => "string", "message" => "string", "postAction" => []])]
    public function createTransaction(float $amount, string $description, string $walletOperationId, string $payerAccountNumber, string $payerOperator, string $payerCurrency): array
    {

        $this->logger->info("# MakutaService > createTransaction : Start");

        // Récupérer le token utilisateur
        $username = $this->params->get('app.username');
        $password = $this->params->get('app.password');

        $userToken = $this->login($username, $password);

//        if (!($userToken) instanceof User) {
//            throw new \RuntimeException("Connection failed", Response::HTTP_UNAUTHORIZED);
//        }

        if ($userToken ===null) {
            throw new \RuntimeException("Connection failed", Response::HTTP_UNAUTHORIZED);
        }

        $this->logger->info('# MakutaService < createTransaction : User token obtained', ['userToken' => $userToken]);

        // Préparer les headers et le body pour l'API de Makuta
        $url = $this->params->get('base.url') . '/api/v2/financial-transactions/0';

        $headers = [
            'Authorization' => 'Bearer ' . $userToken,
            'App-Token' => $this->params->get('app.token'),
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'wallet' => $this->params->get('app.wallet'), // compte transporteur
            'walletOperation' => 'CREDIT',
            'walletAmount' => $amount,
            'clientAccountNumber' => $payerAccountNumber,
            'clientOperator' => $payerOperator,
            'clientCurrency' => $payerCurrency,
            'reason' => $description,
            'thirdPartyReference' => $walletOperationId,
            'isPreview' => false,
            'makeC2B' => true,
        ];

        $this->logger->info('# MakutaService < createTransaction : Sending request to Makuta API', ['payload' => $payload]);

        // Envoyer la requête POST vers Makuta
        $response = $this->httpClient->request('POST', $url, [
            'headers' => $headers,
            'json' => $payload,
        ]);

        $statusCode = $response->getStatusCode();

        // Si le statut n'est pas 200, lever une exception avec le message d'erreur du serveur
        if ($statusCode !== Response::HTTP_OK) {
            $errorContent = $response->toArray(false); // Récupérer le contenu de la réponse
            $errorMessage = $errorContent['message'] ?? $response->getContent(false); // Extraire le message d'erreur si disponible

            $this->logger->error("Transaction failed ::: Si le statut n'est pas 200, lever une exception avec le message d'erreur du serveur", [
                'statusCode' => $statusCode,
                'errorMessage' => $errorMessage
            ]);

            throw new \RuntimeException($errorMessage, $statusCode);
        }

        // Traiter la réponse et extraire le contenu
        $content = $response->toArray();
        $this->logger->info('# MakutaService < createTransaction : Response content processed', ['content' => $content]);

        // Extraire l'ID et l'action
        $makutaId = $content['id'];
        $postAction = $content['postAction'];

        $this->logger->info('Transaction successfully created', [
            'makutaId' => $makutaId,
            'postAction' => $postAction
        ]);

        return [
            'makutaId' => $makutaId,
            'postAction' => $postAction,
        ];
    }


    /**
     * @throws RedirectionExceptionInterface
     * * @throws DecodingExceptionInterface
     * * @throws ClientExceptionInterface
     * * @throws TransportExceptionInterface
     * * @throws ServerExceptionInterface
     */
    public function visaT2PCallback(int $status, string $visaReference, string $transactionId, string $message, string $signature, string $operationType) {
        $this->logger->info("# MakutaService > visaT2PCallback : Start");

        //region Récupérer le token utilisateur
        $username = $this->params->get('app.username');
        $password = $this->params->get('app.password');

        $userToken = $this->login($username, $password);
        //endregion

        if (empty($userToken)) {
            throw new \RuntimeException("Connection failed", Response::HTTP_UNAUTHORIZED);
        }

        //l'url de makuta pour l'envoie du callback
        $url = $this->params->get('base.url') . '/callback/drc/tap-to-phone/ctob-callback-result/'. $transactionId;

        //region headers de la requete pour contacter le serveur makuta
        $headers = [
            'Authorization' => 'Bearer ' . $userToken,
            'App-Token' => $this->params->get('app.token'),
            'Content-Type' => 'application/json',
        ];
        //endregion

        //region corps de la requete pour contacter le serveur makuta
        $payload = [
            'status' => $status,
            'visaReference' => $visaReference,
            'transactionId' => $transactionId,
            'message' => $message,
            'signature' => $signature,
            'operationType' => $operationType
        ];
        //endregion

        $this->logger->info('# MakutaService < visaT2PCallback : envoie de la requête à Makuta', ['payload' => $payload]);

        //region Envoyer la requête POST vers Makuta
        $response = $this->httpClient->request('POST', $url, [
            'headers' => $headers,
            'json' => $payload,
        ]);
        //endregion

        $statusCode = $response->getStatusCode();

        //region Si le code est different de 200 (renvoyer un message d'erreur)
        if ($statusCode !== Response::HTTP_OK) {
            $errorContent = $response->toArray(false); // Récupérer le contenu de la réponse
            $errorMessage = $errorContent['message'] ?? $response->getContent(false); // Extraire le message d'erreur si disponible

            $this->logger->error("Transaction failed ::: Si le statut n'est pas 200, lever une exception avec le message d'erreur du serveur", [
                'statusCode' => $statusCode,
                'errorMessage' => $errorMessage
            ]);

            throw new \RuntimeException($errorMessage, $statusCode);
        }
        //endregion

        //region traitement de la reponse
        $data = $response->toArray();
        $this->logger->info('# MakutaService < visaT2PCallback : Response data', ['content' => $data]);
        //endregion

        return [
          "data" =>  $data
        ];

    }


}