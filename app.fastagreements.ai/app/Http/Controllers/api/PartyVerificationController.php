<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Aggriment;
use App\Models\AgreementPartyVerification;
use App\Models\Customer;
use App\Services\AgreementOtpModeService;
use App\Services\Auth\Msg91OtpService;
use App\Services\PartyVerificationException;
use App\Support\ApiResponse;
use App\Support\MobileNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Phone confirmation for the people named on an agreement / other parties.
 */
class PartyVerificationController extends Controller
{
    public function __construct(
        private readonly AgreementOtpModeService $otpMode,
        private readonly Msg91OtpService $otpService,
    ) {
    }

    /**
     * Sends OTP to a party / user mobile number using MSG91 Template API.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string',
            'template_id' => 'nullable|string',
        ]);

        $mobile = MobileNumber::toStored($request->input('mobile'));

        if (strlen($mobile) !== 10) {
            return ApiResponse::error(
                422,
                'PHONE_UNSUPPORTED',
                'Please provide a valid 10-digit mobile number.',
            );
        }

        $result = $this->otpService->sendOtp($mobile, $request->input('template_id'));

        if (!$result['status']) {
            return ApiResponse::error(400, 'OTP_SEND_FAILED', $result['message']);
        }

        return ApiResponse::ok([
            'mobile' => $mobile,
        ], $result['message']);
    }

    /**
     * Verifies the party OTP and records verification for agreement creation.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string',
            'otp' => 'required|string',
        ]);

        $mobile = MobileNumber::toStored($request->input('mobile'));

        if (strlen($mobile) !== 10) {
            return ApiResponse::error(
                422,
                'PHONE_UNSUPPORTED',
                'Please provide a valid 10-digit mobile number.',
            );
        }

        $result = $this->otpService->verifyOtp($mobile, $request->input('otp'));

        if (!$result['status']) {
            return ApiResponse::error(422, 'INVALID_OTP', $result['message']);
        }

        try {
            $record = $this->otpMode->recordDirectPhoneVerification(
                (int) $request->user()->id,
                $mobile,
            );
        } catch (PartyVerificationException $e) {
            return $e->toResponse();
        }

        return ApiResponse::ok([
            'mobile' => $record['mobile'],
            'verified_at' => $record['verified_at']->toIso8601String(),
            'valid_for_minutes' => AgreementOtpModeService::VERIFICATION_TTL_MINUTES,
        ], 'Party phone verified successfully.');
    }

    /**
     * Records that a party or guarantor confirmed their number via widget access_token.
     */
    public function verifyPhone(Request $request): JsonResponse
    {
        $request->validate(['access_token' => 'required|string']);

        try {
            $result = $this->otpMode->recordPhoneVerification(
                (int) $request->user()->id,
                $request->input('access_token'),
            );
        } catch (PartyVerificationException $e) {
            return $e->toResponse();
        }

        return ApiResponse::ok([
            'mobile' => $result['mobile'],
            'verified_at' => $result['verified_at']->toIso8601String(),
            'valid_for_minutes' => AgreementOtpModeService::VERIFICATION_TTL_MINUTES,
        ], 'Number confirmed.');
    }

    /**
     * Which of the numbers for a prospective agreement are already confirmed.
     *
     * Lets the app draw its checklist before anything is created. Takes the
     * same `party_2_id` / `guarantor_number` fields the create call will take.
     */
    public function pendingForCreation(Request $request): JsonResponse
    {
        $customer = $request->user();

        $data = $request->validate([
            'party_2_id' => 'nullable|integer',
            'guarantor' => 'nullable|string',
            'guarantor_number' => 'nullable|string',
        ]);

        $party2 = isset($data['party_2_id']) ? Customer::find($data['party_2_id']) : null;

        $required = $this->otpMode->requiredForCreation(
            $customer,
            $party2,
            $data['guarantor'] ?? null,
            $data['guarantor_number'] ?? null,
        );

        $pending = [];

        try {
            $this->otpMode->assertVerifiedForCreation(
                (int) $customer->id,
                AgreementOtpModeService::WITH_OTP,
                $required,
            );
        } catch (PartyVerificationException $e) {
            $pending = $e->extra['pending'] ?? [];
        }

        $pendingMobiles = array_column($pending, 'mobile');

        return ApiResponse::ok([
            'people' => array_map(fn (array $person) => [
                'role' => $person['role'],
                'position' => $person['position'],
                'name' => $person['name'],
                'mobile' => $person['mobile'],
                'verified' => !in_array($person['mobile'], $pendingMobiles, true),
            ], $required),
            'all_verified' => $pending === [],
        ]);
    }

    /** Who confirmed on an agreement that already exists. */
    public function forAgreement(Request $request, int $agreementId): JsonResponse
    {
        $agreement = $this->ownedAgreement($request, $agreementId);

        if ($agreement instanceof JsonResponse) {
            return $agreement;
        }

        return ApiResponse::ok([
            'otp_mode' => $agreement->otp_mode,
            'verification_required' => $agreement->otp_mode === AgreementOtpModeService::WITH_OTP,
            'parties' => array_map(fn (AgreementPartyVerification $row) => [
                'role' => $row->role,
                'position' => $row->position,
                'mobile' => $row->mobile,
                'verified' => $row->isVerified(),
                'verified_at' => optional($row->verified_at)->toIso8601String(),
            ], $this->otpMode->verificationRowsFor($agreement)),
        ]);
    }

    /**
     * Loads the agreement only if the caller is one of its two parties.
     *
     * @return Aggriment|JsonResponse
     */
    private function ownedAgreement(Request $request, int $agreementId)
    {
        $customerId = (int) $request->user()->id;

        $agreement = Aggriment::find($agreementId);

        if ($agreement === null
            || ((int) $agreement->party_1_id !== $customerId && (int) $agreement->party_2_id !== $customerId)) {
            // Deliberately the same 404 either way: a distinguishable "forbidden"
            // would let a caller enumerate which agreement ids are real.
            return ApiResponse::error(404, 'AGREEMENT_NOT_FOUND', 'Agreement not found.');
        }

        return $agreement;
    }
}
