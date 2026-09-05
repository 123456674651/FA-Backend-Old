<?php

namespace App\Services\Auth;

/**
 * Proof that somebody holds a phone number.
 *
 * Implementations verify a token issued by an external provider and return the
 * number *that provider* verified. The number must come out of the provider's
 * answer and never out of the request — that difference is the whole reason
 * this abstraction exists rather than the app simply telling us who it is.
 */
interface PhoneIdentityVerifier
{
    /**
     * @return array{uid: string, phone_number: string}
     *
     * @throws PhoneVerificationException when the token is missing, invalid,
     *         expired, or proves no phone number.
     */
    public function verify(string $token): array;
}
