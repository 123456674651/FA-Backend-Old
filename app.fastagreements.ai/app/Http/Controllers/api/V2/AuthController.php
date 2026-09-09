<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Feed;
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
class AuthController extends Controller
{
    public function __construct(private readonly JwtService $jwt)
    {
    }

    public function login(Request $request): JsonResponse
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
            return ApiResponse::error(200, 'OTP_SEND_FAILED', 'Could not send OTP. Please try again.');
        }

        return ApiResponse::ok(['reqId' => $requestId], 'OTP sent successfully.');
    }

    public function verifiedOtp(Request $request): JsonResponse
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
            return ApiResponse::error(200, 'OTP_INVALID', 'The OTP you entered is incorrect.');
        }

        $customer = Customer::where('mobile', $mobile)->first();

        if (!$customer) {
            return ApiResponse::ok([
                'is_new_user' => true,
                'mobile' => $mobile,
            ], 'OTP verified. No account found for this number yet — please complete registration.');
        }

        return ApiResponse::ok([
            'is_new_user' => false,
            'token_type' => 'Bearer',
            'access_token' => $this->jwt->issueForCustomer($customer),
            'user' => $customer,
        ], 'OTP verified successfully.');
    }

    /**
     * Creates a customer account. Same fields, validation, and response
     * shape as the original CustomerController@registertion.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'mobile' => 'required|numeric|min:11|unique:customers',
            'email' => 'email|unique:customers',
            'address' => 'required|regex:/(^[-0-9A-Za-z.,\/ ]+$)/',
            'is_company' => 'required|boolean',
        ])->sometimes(
            ['company_name', 'gst_number'],
            'required|string|max:255',
            fn ($input) => $input->is_company == 1
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $customer = new Customer();
        $customer->name = $request->name;
        $customer->mobile = $request->mobile;
        $customer->email = $request->email;
        $customer->address = $request->address;
        $customer->company_name = $request->company_name ?? null;
        $customer->gst_number = $request->gst_number ?? null;
        $customer->location = $request->location;
        $customer->signature = $request->signature;
        $customer->occupation = $request->occupation;
        $customer->date_of_birth = $request->date_of_birth;
        $customer->gender = $request->gender;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'customer_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/customers'), $filename);
            $customer->photo = 'uploads/customers/' . $filename;
        }

        $customer->save();

        Feed::create([
            'type' => 'customer_joined',
            'customer_id' => $customer->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Registered Successfully',
            'data' => [
                'token_type' => 'Bearer',
                'access_token' => $this->jwt->issueForCustomer($customer),
                'user' => $customer,
            ],
        ]);
    }

    /** Sends the OTP via MSG91's Template API. Returns the request id, or null on failure. */
    private function sendOtpViaTemplate(string $mobile): ?string
    {
        try {
            $response = Http::asForm()->post('https://control.msg91.com/api/v5/otp', [
                'authkey' => config('services.msg91.auth_key'),
                'template_id' => config('services.msg91.template_id'),
                'mobile' => '91' . $mobile,
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
