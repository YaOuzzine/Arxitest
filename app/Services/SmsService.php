<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $twilioClient;

    public function __construct()
    {
        $this->twilioClient = app('sms.client');
    }

    /**
     * Send an SMS message
     *
     * @param string $phone Phone number to send to
     * @param string $message Message to send
     * @return bool Whether the message was sent successfully
     */
    public function send(string $phone, string $message): bool
    {
        try {
            // The correct way to send messages with Twilio
            $this->twilioClient->messages->create(
                $phone,
                [
                    'from' => config('services.twilio.phone_number'),
                    'body' => $message
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send SMS: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send verification code via SMS
     *
     * @param string $phone Phone number to send to
     * @param string $code Verification code to send
     * @return bool Whether the message was sent successfully
     */
    public function sendVerificationCode(string $phone, string $code): bool
    {
        $message = "Your Arxitest verification code is: $code. It will expire in 5 minutes.";
        return $this->send($phone, $message);
    }
}
