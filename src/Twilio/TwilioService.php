<?php


namespace App\Twilio;

use App\Twilio\Verify\VerifyService;
use JetBrains\PhpStorm\ArrayShape;

class TwilioService
{
    private VerifyService $verifyService;

    public function __construct(VerifyService $verifyService)
    {
        $this->verifyService = $verifyService;
    }

    #[ArrayShape(['status' => "string"])]
    public function sendOtp(string $beneficiary, string $channel = "sms")
    {
        return $this->verifyService->sendOtp($beneficiary, $channel);
    }

    #[ArrayShape(['status' => "string"])]
    public function verifyOtp(string $beneficiary, string $otp)
    {
        return $this->verifyService->verifyOtp($beneficiary, $otp);
    }
}