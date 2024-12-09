<?php

namespace App\OneSignal;

use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OneSignalEndpointService
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

//    #[ArrayShape(["status" => "string", "response" => "string", "message" => "string"])]
    public function sendPushNotification(string $externalId, string $code, string $message): array
    {

        $this->logger->info("# OneSignalEndpointService > sendPushNotification : Start");

        $httpClient = HttpClient::create();

        $url = 'https://api.onesignal.com/notifications';
        $headers = [
            'Authorization' => 'Basic os_v2_app_xrfcceombbcwfdezh7mgr3ss4ebbxqxz6c5elp4aek6gu7f3yt6qak2fu33vmbjbsrxq44tvnmhq5wrs5cja5av7oy42m65k5ldsaly',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        $data = [
            'app_id' => 'bc4a2111-cc08-4562-8c99-3fd868ee52e1',
            'target_channel' => 'push',
            'headings' => [
                'en' => 'Makuta Trans',
            ],
            'contents' => [
                'en' => $message,
            ],
            'include_aliases' => [
                'external_id' => [
                    $externalId,
                ],
            ],
            'data'=> [
                'code' => $code,
                'key2' => 'value2'
            ]
        ];

        try {
            $response = $httpClient->request('POST', $url, [
                'headers' => $headers,
                'json' => $data,
            ]);

            $statusCode = $response->getStatusCode(); // 200, 400, etc.
            $content = $response->toArray(); // Convert JSON response to an array

            return [
                'status' => $statusCode,
                'response' => $content,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

    }
}