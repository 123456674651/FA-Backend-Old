<?php

namespace App\Services\Auth;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Handles sending and verifying OTPs directly via MSG91 Template API.
 */
class Msg91OtpService
{
    /**
     * Send OTP to a mobile number using MSG91 OTP Template API.
     *
     * @param string $mobile 10-digit or full mobile number with country code
     * @param string|null $templateId Optional template ID, defaults to config
     * @return array{status: bool, message: string, data: mixed}
     */
    public function sendOtp(string $mobile, ?string $templateId = null, ?int $otpLength = 6, ?int $expiryMinutes = 10): array
    {
        $authKey = $this->authKey();
        $templateId = $templateId ?: config('apiauth.msg91.otp_template_id');

        // Ensure 91 country code prefix for 10-digit Indian numbers if no country code provided
        $formattedMobile = $this->formatMobile($mobile);

        $url = 'https://control.msg91.com/api/v5/otp';

        $params = [
            'template_id' => $templateId,
            'mobile' => $formattedMobile,
            'authkey' => $authKey,
            'otp_length' => $otpLength,
            'otp_expiry' => $expiryMinutes,
        ];

        try {
            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->post($url, $params);
        } catch (ConnectionException $e) {
            Log::error('MSG91 send OTP connection error: ' . $e->getMessage());
            throw new Msg91UnavailableException('Could not reach MSG91 to send OTP. Please try again.');
        }

        $body = $response->json() ?? [];

        if (($body['type'] ?? '') === 'success') {
            return [
                'status' => true,
                'message' => $body['message'] ?? 'OTP sent successfully.',
                'data' => $body,
            ];
        }

        Log::error('MSG91 send OTP failed: ' . json_encode($body));
        return [
            'status' => false,
            'message' => $body['message'] ?? 'Failed to send OTP.',
            'data' => $body,
        ];
    }

    /**
     * Verify OTP for a mobile number via MSG91 OTP Verify API.
     *
     * @param string $mobile
     * @param string $otp
     * @return array{status: bool, message: string, data: mixed}
     */
    public function verifyOtp(string $mobile, string $otp): array
    {
        $authKey = $this->authKey();
        $formattedMobile = $this->formatMobile($mobile);

        $url = 'https://control.msg91.com/api/v5/otp/verify';

        try {
            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->get($url, [
                    'authkey' => $authKey,
                    'mobile' => $formattedMobile,
                    'otp' => $otp,
                ]);
        } catch (ConnectionException $e) {
            Log::error('MSG91 verify OTP connection error: ' . $e->getMessage());
            throw new Msg91UnavailableException('Could not reach MSG91 to verify OTP. Please try again.');
        }

        $body = $response->json() ?? [];

        if (($body['type'] ?? '') === 'success') {
            return [
                'status' => true,
                'message' => $body['message'] ?? 'OTP verified successfully.',
                'data' => $body,
            ];
        }

        Log::info('MSG91 OTP verification failed: ' . json_encode($body));
        return [
            'status' => false,
            'message' => $body['message'] ?? 'Invalid OTP or OTP expired.',
            'data' => $body,
        ];
    }

    /**
     * Resend OTP via SMS or Voice.
     *
     * @param string $mobile
     * @param string $retryType 'voice' or 'text'
     * @return array{status: bool, message: string, data: mixed}
     */
    public function resendOtp(string $mobile, string $retryType = 'text'): array
    {
        $authKey = $this->authKey();
        $formattedMobile = $this->formatMobile($mobile);

        $url = 'https://control.msg91.com/api/v5/otp/retry';

        try {
            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->get($url, [
                    'authkey' => $authKey,
                    'mobile' => $formattedMobile,
                    'retrytype' => $retryType,
                ]);
        } catch (ConnectionException $e) {
            Log::error('MSG91 retry OTP connection error: ' . $e->getMessage());
            throw new Msg91UnavailableException('Could not reach MSG91 to retry OTP. Please try again.');
        }

        $body = $response->json() ?? [];

        return [
            'status' => ($body['type'] ?? '') === 'success',
            'message' => $body['message'] ?? 'OTP resent.',
            'data' => $body,
        ];
    }

    private function authKey(): string
    {
        $authKey = config('apiauth.msg91.auth_key');

        if (!is_string($authKey) || $authKey === '') {
            throw new RuntimeException('MSG91_AUTH_KEY is not configured in .env.');
        }

        return $authKey;
    }

    private function timeout(): int
    {
        return max(1, (int) config('apiauth.msg91.timeout', 10));
    }

    private function formatMobile(string $mobile): string
    {
        $clean = preg_replace('/[^\d]/', '', $mobile);

        // If 10 digits, prepend 91
        if (strlen($clean) === 10) {
            return '91' . $clean;
        }

        return $clean;
    }
}
