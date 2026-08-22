<?php

namespace App\Services;

use App\Models\Aggriment;
use App\Models\AgreementPartyVerification;
use App\Models\Customer;
use App\Models\DealCategory;
use App\Models\PartyPhoneVerification;
use App\Services\Auth\FirebaseIdTokenVerifier;
use Carbon\Carbon;

/**
 * The with-OTP / without-OTP tier.
 *
 * The choice does two things. It prices the agreement — a category may charge
 * differently for the verified tier — and it decides whether every party and
 * guarantor must confirm their phone number before the agreement can be
 * created. Under `without_otp` their presence on the form is enough, which is
 * exactly what the customer chose and paid a different price for.
 *
 * On sequencing: `create_aggriment` builds the agreement row and renders the
 * document in one call, so there is no window in which an existing agreement
 * is waiting on confirmations. Verification therefore happens against the
 * phone numbers *before* creation (see PartyPhoneVerification), and those
 * confirmations are snapshotted onto the agreement once it exists.
 */
class AgreementOtpModeService
{
    public const WITH_OTP = 'with_otp';
    public const WITHOUT_OTP = 'without_otp';

    /** At most four guarantors, matching the exploded legacy string. */
    public const MAX_GUARANTORS = 4;

    /**
     * How long a confirmation stays spendable.
     *
     * Long enough to fill in a long form after confirming everyone, short
     * enough that a number confirmed months ago cannot silently authorise a
     * new agreement today.
     */
    public const VERIFICATION_TTL_MINUTES = 120;

    public function __construct(private readonly FirebaseIdTokenVerifier $firebase)
    {
    }

    /** @return array<int, string> */
    public static function modes(): array
    {
        return [self::WITH_OTP, self::WITHOUT_OTP];
    }

    /**
     * What this agreement costs under the chosen tier.
     *
     * Falls back to the category's flat `deal_price` whenever the per-tier
     * price is unset, so a category that has never been given split pricing
     * keeps charging what it always charged.
     */
    public function resolvePrice(DealCategory $category, ?string $mode): float
    {
        $flat = (float) ($category->deal_price ?? 0);

        $tiered = match ($mode) {
            self::WITH_OTP => $category->price_with_otp,
            self::WITHOUT_OTP => $category->price_without_otp,
            default => null,
        };

        return $tiered === null ? $flat : (float) $tiered;
    }

    // ---------------------------------------------------------------------
    // Before the agreement exists
    // ---------------------------------------------------------------------

    /**
     * Records that a number was confirmed, from the Firebase ID token the
     * phone verification produced.
     *
     * The token is signed by Google and carries the number Firebase actually
     * verified, so unlike the code this replaces there is nothing here the
     * client can fabricate.
     *
     * @return array{mobile: string, verified_at: Carbon}
     *
     * @throws PartyVerificationException
     */
    public function recordPhoneVerification(int $customerId, string $idToken): array
    {
        // Throws FirebaseTokenException, rendered as 401 by the exception handler.
        $identity = $this->firebase->verify($idToken);

        $mobile = FirebaseIdTokenVerifier::toStoredMobile($identity['phone_number']);

        if (strlen($mobile) !== 10) {
            throw new PartyVerificationException(
                422,
                'PHONE_UNSUPPORTED',
                'The verified number is not a 10-digit mobile.',
            );
        }

        $verifiedAt = Carbon::now();

        PartyPhoneVerification::updateOrCreate(
            ['customer_id' => $customerId, 'mobile' => $mobile],
            ['firebase_uid' => $identity['uid'], 'verified_at' => $verifiedAt],
        );

        return ['mobile' => $mobile, 'verified_at' => $verifiedAt];
    }

    /**
     * The numbers a new agreement needs confirmed.
     *
     * @return array<int, array{role: string, position: int, mobile: string, name: string}>
     */
    public function requiredForCreation(
        ?Customer $party1,
        ?Customer $party2,
        ?string $guarantorNames,
        ?string $guarantorNumbers,
    ): array {
        $required = [];

        foreach ([
            AgreementPartyVerification::ROLE_PARTY_1 => $party1,
            AgreementPartyVerification::ROLE_PARTY_2 => $party2,
        ] as $role => $customer) {
            $mobile = $this->normaliseMobile($customer->mobile ?? null);

            if ($mobile !== null) {
                $required[] = [
                    'role' => $role,
                    'position' => 0,
                    'mobile' => $mobile,
                    'name' => (string) ($customer->name ?? ''),
                ];
            }
        }

        $names = $this->explodeLegacyList($guarantorNames);
        $numbers = $this->explodeLegacyList($guarantorNumbers);

        for ($position = 0; $position < self::MAX_GUARANTORS; $position++) {
            $mobile = $this->normaliseMobile($numbers[$position] ?? null);

            if ($mobile === null) {
                continue;
            }

            $required[] = [
                'role' => AgreementPartyVerification::ROLE_GUARANTOR,
                'position' => $position,
                'mobile' => $mobile,
                'name' => trim((string) ($names[$position] ?? '')),
            ];
        }

        return $required;
    }

    /**
     * The creation gate.
     *
     * Under `with_otp` every listed number must carry a fresh confirmation by
     * this customer. Under `without_otp` — and for a null mode, which means the
     * caller predates the feature — there is nothing to check.
     *
     * @param  array<int, array{role: string, position: int, mobile: string, name: string}>  $required
     *
     * @throws PartyVerificationException
     */
    public function assertVerifiedForCreation(int $customerId, ?string $mode, array $required): void
    {
        if ($mode !== self::WITH_OTP) {
            return;
        }

        if ($required === []) {
            throw new PartyVerificationException(
                422,
                'PARTIES_INCOMPLETE',
                'This agreement has nobody to verify.',
            );
        }

        $verified = $this->freshVerifications($customerId, array_column($required, 'mobile'));

        $pending = [];

        foreach ($required as $person) {
            if (!isset($verified[$person['mobile']])) {
                $pending[] = [
                    'role' => $person['role'],
                    'position' => $person['position'],
                    'name' => $person['name'],
                    'mobile' => $person['mobile'],
                ];
            }
        }

        if ($pending !== []) {
            $labels = array_map(
                fn (array $p) => $p['name'] !== '' ? $p['name'] : $p['mobile'],
                $pending,
            );

            throw new PartyVerificationException(
                422,
                'PARTIES_NOT_VERIFIED',
                'Still waiting on OTP confirmation from: ' . implode(', ', $labels) . '.',
                ['pending' => $pending],
            );
        }
    }

    /**
     * Copies the confirmations onto the agreement once it exists, so the
     * document has a permanent record of who confirmed and when.
     *
     * Under `without_otp` the rows are still written, marked unverified, which
     * is what lets the checklist endpoint show the parties either way.
     *
     * @param  array<int, array{role: string, position: int, mobile: string, name: string}>  $required
     */
    public function snapshotForAgreement(Aggriment $agreement, int $customerId, array $required): void
    {
        if ($required === []) {
            return;
        }

        $verified = $this->freshVerifications($customerId, array_column($required, 'mobile'));

        foreach ($required as $person) {
            $proof = $verified[$person['mobile']] ?? null;

            AgreementPartyVerification::updateOrCreate(
                [
                    'agreement_id' => $agreement->id,
                    'role' => $person['role'],
                    'position' => $person['position'],
                ],
                [
                    'mobile' => $person['mobile'],
                    'verified_at' => $proof?->verified_at,
                    'firebase_uid' => $proof?->firebase_uid,
                    'verified_via' => $proof !== null
                        ? AgreementPartyVerification::VIA_FIREBASE
                        : AgreementPartyVerification::VIA_NONE,
                ],
            );
        }
    }

    // ---------------------------------------------------------------------
    // After the agreement exists
    // ---------------------------------------------------------------------

    /**
     * The stored verification rows for an agreement. Read-only: this reports
     * what happened at creation, it does not create anything.
     *
     * @return array<int, AgreementPartyVerification>
     */
    public function verificationRowsFor(Aggriment $agreement): array
    {
        return AgreementPartyVerification::query()
            ->where('agreement_id', $agreement->id)
            ->orderBy('role')
            ->orderBy('position')
            ->get()
            ->all();
    }

    /**
     * Guard for regenerating an existing agreement's document.
     *
     * @throws PartyVerificationException
     */
    public function assertPartiesVerified(Aggriment $agreement): void
    {
        if ($agreement->otp_mode !== self::WITH_OTP) {
            return;
        }

        $pending = [];

        foreach ($this->verificationRowsFor($agreement) as $row) {
            if (!$row->isVerified()) {
                $pending[] = $row->mobile;
            }
        }

        if ($pending !== []) {
            throw new PartyVerificationException(
                422,
                'PARTIES_NOT_VERIFIED',
                'Still waiting on OTP confirmation from: ' . implode(', ', $pending) . '.',
                ['pending' => $pending],
            );
        }
    }

    // ---------------------------------------------------------------------

    /**
     * Confirmations still inside the freshness window, keyed by mobile.
     *
     * @param  array<int, string>  $mobiles
     * @return array<string, PartyPhoneVerification>
     */
    private function freshVerifications(int $customerId, array $mobiles): array
    {
        if ($mobiles === []) {
            return [];
        }

        return PartyPhoneVerification::query()
            ->where('customer_id', $customerId)
            ->whereIn('mobile', array_unique($mobiles))
            ->where('verified_at', '>=', Carbon::now()->subMinutes(self::VERIFICATION_TTL_MINUTES))
            ->get()
            ->keyBy('mobile')
            ->all();
    }

    /** @return array<int, string> */
    private function explodeLegacyList(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        return array_map('trim', explode(',', $value));
    }

    /** Reduces to the ten digits the customers table holds, or null if unusable. */
    private function normaliseMobile(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $mobile = FirebaseIdTokenVerifier::toStoredMobile($value);

        return strlen($mobile) === 10 ? $mobile : null;
    }
}
