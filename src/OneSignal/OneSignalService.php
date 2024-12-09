<?php

namespace App\OneSignal;

use App\Entity\User;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OneSignalService
{
    private LoggerInterface $logger;
    private HttpClientInterface $httpClient;
    private ParameterBagInterface $params;
    private OneSignalEndpointService $oes;
    private Security $security;

    public function __construct(LoggerInterface $logger, HttpClientInterface $httpClient, ParameterBagInterface $params, OneSignalEndpointService $oes,Security $security)
    {
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->params = $params;
        $this->oes = $oes;
        $this->security = $security;
    }

//    #[ArrayShape(["status" => "string", "response" => "string", "message" => "string"])]
    public function sendPushNotification(string $title, string $message, $id, array $custom=[]): array
    {
        $this->logger->info("# OneSignalService > sendPushNotification : Start");

        $externalId = $id;

        return $result = $this->oes->sendPushNotification($externalId,$title,$message,$custom);

    }
}