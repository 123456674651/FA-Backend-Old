<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Aggriment;
use App\Models\AgreementPartyVerification;
use App\Models\Customer;
use App\Services\AgreementOtpModeService;
use App\Services\PartyVerificationException;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Phone confirmation for the people named on an agreement.
 *
 * Confirmations are collected before the agreement is created — see
 * AgreementOtpModeService for why — and read back afterwards as a record of
 * who confirmed.
 */
class PartyVerificationController extends Controller
{
    public function __construct(private readonly AgreementOtpModeService $otpMode)
    {
    }

    /**
     * Records that a party or guarantor confirmed their number.
     *
     * Called once per person, before `create_aggriment`. The app runs Firebase
     * phone verification on the handset and posts the resulting ID token here;
     * the number inside that token is what gets recorded, not anything the
     * client asserts.
     */
    public function verifyPhone(Request $request): JsonResponse
    {
        $request->validate(['id_token' => 'required|string']);

        try {
            $result = $this->otpMode->recordPhoneVerification(
                (int) $request->user()->id,
                $request->input('id_token'),
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
