<?php

namespace App\Service;

use App\Twilio\TwilioService;
use Psr\Log\LoggerInterface;

class NotificationService
{
    private LoggerInterface $logger;
    private ArrayService $arrayService;
    private TwilioService $twilioService;

    public function __construct(LoggerInterface $logger, ArrayService $arrayService, TwilioService $twilioService)
    {
        $this->logger = $logger;
        $this->arrayService = $arrayService;
        $this->twilioService = $twilioService;
    }

    public function sendOtp(array $data): void
    {
        $this->logger->info("# NotificationService > sendOtp: Start", $data);

        $structure = ['beneficiary', 'channel'];
        $this->arrayService->array_diff($structure, $data);

        $this->logger->info("# NotificationService > sendOtp: Before send OTP", ['beneficiary' => $data['beneficiary'], 'channel' => $data['channel']]);
        $result = $this->twilioService->sendOtp($data['beneficiary'], $data['channel']);
        $this->logger->info("# NotificationService > sendOtp: After send OTP successfully", ['beneficiary' => $data['beneficiary'], 'channel' => $data['channel'], 'result' => $result]);

    }

    public function verifyOtp(string $beneficiary, string $otp): void
    {
        $this->logger->info("# NotificationService > verifyOtp: Start", ['beneficiary' => $beneficiary, 'otp' => $otp]);
        $result = $this->twilioService->verifyOtp($beneficiary, $otp);
        $this->logger->info("# NotificationService > verifyOtp: Start", ['beneficiary' => $beneficiary, 'otp' => $otp, 'result' => $result]);
    }
}