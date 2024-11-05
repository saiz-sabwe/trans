<?php


namespace App\Twilio\Verify;


use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Rest\Client;

class VerifyService
{
    static public array $status = ['APPROVED' => 'APPROVED', 'PENDING' => 'PENDING', 'CANCELED' => 'CANCELED'];

    private ParameterBagInterface $bag;
    private LoggerInterface $logger;

    public function __construct(ParameterBagInterface $bag, LoggerInterface $logger)
    {
        $this->bag = $bag;
        $this->logger = $logger;
    }

    #[ArrayShape(['status' => "string"])]
    public function sendOtp(string $beneficiary, string $channel = "sms"): array
    {
        $twilio = $this->bag->get('twilio');

        $client = new Client($twilio['sms']['account_sid'], $twilio['sms']['auth_token']);

        $realNumber = str_contains($beneficiary, "+") ? $beneficiary : "+" . $beneficiary;

        $verification = $client->verify->v2->services("VA65b768f5a8799d2ee8f7c71a73411c92")
            ->verifications
            ->create($realNumber, $channel);


        $this->logger->info("# VerifyService > sendOtp: Response", [$verification]);

        return [
            'status' => $verification->status
        ];
    }

    #[ArrayShape(['status' => "string"])]
    public function verifyOtp(string $beneficiary, string $otp): array
    {
        $twilio = $this->bag->get('twilio');

        $client = new Client($twilio['sms']['account_sid'], $twilio['sms']['auth_token']);

        $realNumber = str_contains($beneficiary, "+") ? $beneficiary : "+" . $beneficiary;

        $this->logger->info("# Send OTP data", [
            "beneficiary" => $beneficiary,
            "realNumber" => $realNumber,
            "otp" => $otp,
        ]);

        $verificationCheck = $client->verify->v2->services("VA65b768f5a8799d2ee8f7c71a73411c92")
            ->verificationChecks
            ->create([
                    "to" => $realNumber,
                    "code" => $otp
                ]
            );

        $this->logger->info(">>>>> OTP Check Response", [$verificationCheck]);

        $status = strtoupper($verificationCheck->status);

        if($status === self::$status['CANCELED'])
        {
            throw new \RuntimeException("Désolé, le code de sécurité a été annulé", Response::HTTP_UNAUTHORIZED);
        }

        if($status === self::$status['PENDING'])
        {
            throw new \RuntimeException("Désolé, le code de sécurité est en attente de validation", Response::HTTP_UNAUTHORIZED);
        }

        return [
            'status' => $verificationCheck->status
        ];
    }
}