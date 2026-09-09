<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\MobileNumber;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Common OTP send/verify. Only for an already-logged-in customer (e.g.
 * confirming a new mobile number) — not the login flow, which has its own
 * send/verify in AuthController.
 *
 * If `MSG91_OTP_TEMPLATE_ID` is set in .env, the Template API is used;
 * otherwise it falls back to the Widget API (`MSG91_WIDGET_ID`).
 */
class CommonController extends Controller
{
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|numeric',
        ]);

        $mobile = MobileNumber::toStored($request->input('mobile'));

        if (config('services.msg91.template_id')) {
            $requestId = $this->sendOtpViaTemplate($mobile);
        } else {
            $requestId = $this->sendOtpViaWidget($mobile);
        }

        if (!$requestId) {
            return ApiResponse::error(422, 'OTP_SEND_FAILED', 'Could not send OTP. Please try again.');
        }

        return ApiResponse::ok(['reqId' => $requestId], 'OTP sent successfully.');
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $usingTemplate = (bool) config('services.msg91.template_id');

        $request->validate([
            'mobile' => 'required|numeric',
            'otp' => 'required|numeric',
            'reqId' => $usingTemplate ? 'nullable|string' : 'required|string',
        ]);

        $mobile = MobileNumber::toStored($request->input('mobile'));
        $otp = $request->input('otp');

        if ($usingTemplate) {
            $verified = $this->verifyOtpViaTemplate($mobile, $otp);
        } else {
            $verified = $this->verifyOtpViaWidget($mobile, $otp, $request->input('reqId'));
        }

        if (!$verified) {
            return ApiResponse::error(422, 'OTP_INVALID', 'The OTP you entered is incorrect.');
        }

        return ApiResponse::ok(['mobile' => $mobile], 'OTP verified successfully.');
    }

    /** Sends the OTP via MSG91's Template API. Returns the request id, or null on failure. */
    private function sendOtpViaTemplate(string $mobile): ?string
    {
        try {
            $response = Http::asForm()->post('https://control.msg91.com/api/v5/otp', [
                'authkey' => config('services.msg91.auth_key'),
                'template_id' => config('services.msg91.template_id'),
                'mobile' => '91' . $mobile,
            ]);
        } catch (ConnectionException $e) {
            $this->logMsg91ConnectionFailure('send (template)', $mobile, $e);

            return null;
        }

        if ($response->json('type') === 'success') {
            return $response->json('request_id');
        }

        $this->logMsg91Failure('send (template)', $mobile, $response);

        return null;
    }

    /** Sends the OTP via MSG91's Widget API. Returns the request id, or null on failure. */
    private function sendOtpViaWidget(string $mobile): ?string
    {
        try {
            $response = Http::withHeaders(['authkey' => config('services.msg91.auth_key')])
                ->post('https://api.msg91.com/api/v5/widget/sendOtp', [
                    'widgetId' => config('services.msg91.widget_id'),
                    'identifier' => '91' . $mobile,
                ]);
        } catch (ConnectionException $e) {
            $this->logMsg91ConnectionFailure('send (widget)', $mobile, $e);

            return null;
        }

        if ($response->json('type') === 'success') {
            return $response->json('message');
        }

        $this->logMsg91Failure('send (widget)', $mobile, $response);

        return null;
    }

    /** Verifies the OTP via MSG91's Template API. */
    private function verifyOtpViaTemplate(string $mobile, string $otp): bool
    {
        $response = Http::get('https://control.msg91.com/api/v5/otp/verify', [
            'authkey' => config('services.msg91.auth_key'),
            'mobile' => '91' . $mobile,
            'otp' => $otp,
        ]);

        if ($response->json('type') === 'success') {
            return true;
        }

        $this->logMsg91Failure('verify (template)', $mobile, $response);

        return false;
    }

    /** Verifies the OTP via MSG91's Widget API. */
    private function verifyOtpViaWidget(string $mobile, string $otp, ?string $requestId): bool
    {
        $response = Http::withHeaders(['authkey' => config('services.msg91.auth_key')])
            ->post('https://api.msg91.com/api/v5/widget/verifyOtp', [
                'widgetId' => config('services.msg91.widget_id'),
                'reqId' => $requestId,
                'otp' => $otp,
            ]);

        if ($response->json('type') === 'success') {
            return true;
        }

        $this->logMsg91Failure('verify (widget)', $mobile, $response);

        return false;
    }

    /** Writes the mobile, HTTP status and full MSG91 body to the log so a failure is diagnosable. */
    private function logMsg91Failure(string $step, string $mobile, HttpResponse $response): void
    {
        Log::error("MSG91 OTP {$step} failed.", [
            'mobile' => $mobile,
            'http_status' => $response->status(),
            'response' => $response->json() ?? $response->body(),
        ]);
    }

    /** Writes a network-level failure (timeout, DNS, SSL, ...) — the request never reached MSG91. */
    private function logMsg91ConnectionFailure(string $step, string $mobile, ConnectionException $e): void
    {
        Log::error("MSG91 OTP {$step} could not connect.", [
            'mobile' => $mobile,
            'error' => $e->getMessage(),
        ]);
    }
}
