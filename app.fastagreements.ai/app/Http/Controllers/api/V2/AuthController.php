<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\api\BaseController;
use App\Models\Customer;
use App\Models\Feed;
use App\Models\User;
use App\Models\UserToken;
use App\Services\Auth\JwtService;
use App\Support\ApiResponse;
use App\Support\MobileNumber;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Mobile login with an MSG91 OTP.
 *
 * Step 1 (login): send an OTP. For the Widget API, MSG91's request id is
 * handed back to the client so it can be sent along with the OTP.
 * Step 2 (verifiedOtp): confirm the OTP with MSG91, then log the matching
 * customer in.
 *
 * MSG91 has two ways to send/verify an OTP. If `MSG91_OTP_TEMPLATE_ID` is
 * set in .env, the Template API is used; otherwise it falls back to the
 * Widget API (`MSG91_WIDGET_ID`).
 */
class AuthController extends BaseController
{
    public function __construct(private readonly JwtService $jwt)
    {
    }

    public function login(Request $request): JsonResponse
    {
        \Illuminate\Support\Facades\Log::info('Login Request Data:', $request->all());

        $request->validate([
            'mobile' => 'required|numeric',
        ]);

        $mobile = MobileNumber::toStored($request->input('mobile'));

        // $users = User::where('mobile', $mobile)->first();

        // if (empty($users)) {
        //     $resp = $this->sendError('User not found');
        //     \Illuminate\Support\Facades\Log::info('Login Response Data:', (array) $resp->getData());
        //     return $resp;
        // }

        if (config('services.msg91.template_id')) {
            $requestId = $this->sendOtpViaTemplate($mobile);
        } else {
            $requestId = $this->sendOtpViaWidget($mobile);
        }

        if (!$requestId) {
            $resp = $this->sendError('Could not send OTP. Please try again.');
            \Illuminate\Support\Facades\Log::info('Login Response Data:', (array) $resp->getData());
            return $resp;
        }

        $resp = $this->sendResponse(['reqId' => $requestId], 'OTP sent successfully.');
        \Illuminate\Support\Facades\Log::info('Login Response Data:', (array) $resp->getData());
        return $resp;
    }

    public function verifiedOtp(Request $request): JsonResponse
    {
        \Illuminate\Support\Facades\Log::info('Verified OTP Request Data:', $request->all());

        $usingTemplate = (bool) config('services.msg91.template_id');

        $request->validate([
            'mobile' => 'required|numeric',
            'otp' => 'required|numeric',
            'reqId' => $usingTemplate ? 'nullable|string' : 'required|string',
            'device_id' => 'nullable|string',
            'fcm_token' => 'nullable|string',
            'device_type' => 'nullable|string|in:android,ios,web',
            'device_name' => 'nullable|string',
            'app_version' => 'nullable|string',
        ]);

        $mobile = MobileNumber::toStored($request->input('mobile'));
        $otp = $request->input('otp');

        if ($usingTemplate) {
            $verified = $this->verifyOtpViaTemplate($mobile, $otp);
        } else {
            $verified = $this->verifyOtpViaWidget($mobile, $otp, $request->input('reqId'));
        }

        if (!$verified) {
            $resp = $this->sendError('The OTP you entered is incorrect.', [], 200, 'OTP_INVALID');
            \Illuminate\Support\Facades\Log::info('Verified OTP Response Data:', (array) $resp->getData());
            return $resp;
        }

        $user = User::where('mobile', $mobile)->first();

        if (!$user) {
            $resp = $this->sendResponse([
                'is_new_user' => true,
                'mobile' => $mobile,
            ], 'OTP verified. No account found for this number yet — please complete registration.');
            \Illuminate\Support\Facades\Log::info('Verified OTP Response Data:', (array) $resp->getData());
            return $resp;
        }

        $accessToken = $this->jwt->issueForCustomer($user);

        // Store or update access_token & FCM token
        $this->syncUserToken($user, $request, $accessToken);

        $resp = $this->sendResponse([
            'is_new_user' => false,
            'token_type' => 'Bearer',
            'access_token' => $accessToken,
            'user' => $user,
        ], 'OTP verified successfully.');
        \Illuminate\Support\Facades\Log::info('Verified OTP Response Data:', (array) $resp->getData());
        return $resp;
    }

    /**
     * Creates an account. Same fields, validation, and response shape as
     * the original CustomerController@registertion, but stores the new
     * account in `users` instead of `customers` — every new signup now
     * belongs in the users table.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'mobile' => 'required|numeric|min:10',
            'email' => 'email|unique:users',
            'address' => 'required|string',
            'is_company' => 'required|boolean',
            'fcm_token' => 'nullable|string',
            'device_type' => 'nullable|string|in:android,ios,web',
            'device_name' => 'nullable|string',
        ])->sometimes(
                ['company_name', 'gst_number'],
                'required|string|max:255',
                fn($input) => $input->is_company == 1
            );

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }

        $mobile = MobileNumber::toStored($request->input('mobile'));

        if (User::where('mobile', $mobile)->exists()) {
            return $this->sendError('This mobile number is already registered.');
        }

        $user = new User();
        $user->name = $request->name;
        $user->mobile = $mobile;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->is_company = $request->is_company;
        $user->company_name = $request->company_name ?? null;
        $user->gst_number = $request->gst_number ?? null;
        $user->location = $request->location;
        $user->signature = $request->signature;
        $user->occupation = $request->occupation;
        $user->date_of_birth = $request->date_of_birth;
        $user->gender = $request->gender;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'user_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/profiles'), $filename);
            $user->profile_picture = $filename;
        }

        $user->save();

        // Feed::create([
        //     'type' => 'customer_joined',
        //     'customer_id' => $user->id,
        // ]);

        $accessToken = $this->jwt->issueForCustomer($user);

        // Store or update access_token & FCM token
        $this->syncUserToken($user, $request, $accessToken);

        return $this->sendResponse([
            'token_type' => 'Bearer',
            'access_token' => $accessToken,
            'user' => $user,
        ], 'Registered Successfully');
    }

    /**
     * Logout and remove current session / FCM token record.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $fcmToken = $request->input('fcm_token');

        if ($user) {
            $query = UserToken::where('user_id', $user->id);
            if ($fcmToken) {
                $query->where('fcm_token', $fcmToken);
            }
            $query->delete();
        }

        return $this->sendResponse([], 'Logged out successfully.');
    }

    /**
     * Helper to sync or create token/session info & FCM token for the user.
     */
    private function syncUserToken(User $user, Request $request, ?string $accessToken = null): void
    {
        $fcmToken = $request->input('fcm_token');

        UserToken::create([
            'user_id' => $user->id,
            'access_token' => $accessToken,
            'fcm_token' => $fcmToken,
            'device_type' => $request->input('device_type', 'android'),
            'last_active_at' => now(),
        ]);
    }

    /** Sends the OTP via MSG91's Template API. Returns the request id, or null on failure. */
    private function sendOtpViaTemplate(string $mobile): ?string
    {
        $msg91Mobile = (strlen($mobile) == 10) ? '91' . $mobile : $mobile;

        try {
            $response = Http::asForm()->post('https://control.msg91.com/api/v5/otp', [
                'authkey' => config('services.msg91.auth_key'),
                'template_id' => config('services.msg91.template_id'),
                'mobile' => $msg91Mobile,
                'otp_length' => 6,
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
        $msg91Mobile = (strlen($mobile) == 10) ? '91' . $mobile : $mobile;

        try {
            $response = Http::withHeaders(['authkey' => config('services.msg91.auth_key')])
                ->post('https://api.msg91.com/api/v5/widget/sendOtp', [
                    'widgetId' => config('services.msg91.widget_id'),
                    'identifier' => $msg91Mobile,
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
        $msg91Mobile = (strlen($mobile) == 10) ? '91' . $mobile : $mobile;

        $response = Http::get('https://control.msg91.com/api/v5/otp/verify', [
            'authkey' => config('services.msg91.auth_key'),
            'mobile' => $msg91Mobile,
            'otp' => $otp,
        ]);

        if ($response->json('type') === 'success') {
            return true;
        }

        $message = strtolower((string) $response->json('message'));
        if (str_contains($message, 'already verified')) {
            return true; // Treat already verified as success
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

        $message = strtolower((string) $response->json('message'));
        if (str_contains($message, 'already verified')) {
            return true; // Treat already verified as success
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
