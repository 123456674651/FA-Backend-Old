<?php

namespace App\Services\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Verifies Firebase Phone Auth ID tokens.
 *
 * Firebase is this project's only SMS gateway, so the code is sent to the
 * handset and checked on the device — the server never sees it. What the
 * server can do is refuse to take the app's word for the outcome: a Firebase
 * ID token is signed by Google and carries a `phone_number` claim that only a
 * real verification produces. Checking that signature is the whole difference
 * between "this person confirmed their number" and "the client said so".
 *
 * Only a project id is needed. Verification uses Google's *public* x509
 * certificates, so unlike FCM this requires no service account.
 */
class FirebaseIdTokenVerifier
{
    /**
     * @return array{uid: string, phone_number: string}
     *
     * @throws FirebaseTokenException for anything suspect — wrong project, bad
     *         signature, expired, or a sign-in method that proves no phone number.
     */
    public function verify(string $idToken): array
    {
        $projectId = $this->projectId();

        $keys = $this->signingKeys();

        JWT::$leeway = (int) config('apiauth.firebase.leeway', 60);

        try {
            $decoded = (array) JWT::decode($idToken, $keys);
        } catch (Throwable) {
            throw new FirebaseTokenException('The phone verification token is invalid or has expired.');
        }

        // php-jwt checks the signature and the time claims. Everything that
        // ties the token to *this* Firebase project is on us.
        $expectedIssuer = 'https://securetoken.google.com/' . $projectId;

        if (($decoded['iss'] ?? null) !== $expectedIssuer) {
            throw new FirebaseTokenException('The phone verification token was issued for a different project.');
        }

        if (($decoded['aud'] ?? null) !== $projectId) {
            throw new FirebaseTokenException('The phone verification token was issued for a different project.');
        }

        $uid = (string) ($decoded['sub'] ?? '');
        if ($uid === '') {
            throw new FirebaseTokenException('The phone verification token is missing its subject.');
        }

        // Present only for Phone Auth sign-ins. A Google or email sign-in
        // produces a perfectly valid token that proves no phone number at all,
        // so its absence has to be a rejection rather than a null.
        $phoneNumber = (string) ($decoded['phone_number'] ?? '');
        if ($phoneNumber === '') {
            throw new FirebaseTokenException('That sign-in method does not confirm a phone number.');
        }

        return ['uid' => $uid, 'phone_number' => $phoneNumber];
    }

    /**
     * Reduces an E.164 number (+919876543210) to the ten digits the
     * `customers.mobile` column holds, so a customer resolves to one row
     * however they signed in.
     */
    public static function toStoredMobile(string $phoneNumber): string
    {
        $digits = preg_replace('/\D/', '', $phoneNumber) ?? '';

        return strlen($digits) > 10 ? substr($digits, -10) : $digits;
    }

    /**
     * Google's signing certificates, keyed by `kid`.
     *
     * They rotate these roughly daily and say when via Cache-Control, so the
     * cache honours their own max-age rather than a number picked here.
     *
     * @return array<string, Key>
     */
    private function signingKeys(): array
    {
        $cacheKey = (string) config('apiauth.firebase.certs_cache_key', 'firebase_securetoken_certs');

        $certificates = Cache::get($cacheKey);

        if (!is_array($certificates) || $certificates === []) {
            $certificates = $this->fetchCertificates($cacheKey);
        }

        $keys = [];

        foreach ($certificates as $kid => $pem) {
            $certificate = openssl_x509_read($pem);

            if ($certificate === false) {
                continue;
            }

            $keys[(string) $kid] = new Key($certificate, 'RS256');
        }

        if ($keys === []) {
            throw new FirebaseTokenException('Could not load Google signing certificates.');
        }

        return $keys;
    }

    /** @return array<string, string> */
    private function fetchCertificates(string $cacheKey): array
    {
        $url = (string) config('apiauth.firebase.certs_url');

        try {
            $response = Http::timeout(10)->get($url);
        } catch (Throwable) {
            throw new FirebaseTokenException('Could not reach Google to verify the phone number. Please try again.');
        }

        if (!$response->successful()) {
            throw new FirebaseTokenException('Could not reach Google to verify the phone number. Please try again.');
        }

        $certificates = $response->json();

        if (!is_array($certificates) || $certificates === []) {
            throw new FirebaseTokenException('Could not load Google signing certificates.');
        }

        $ttl = $this->cacheTtlFrom($response->header('Cache-Control'));

        Cache::put($cacheKey, $certificates, $ttl);

        return $certificates;
    }

    /** Honours Google's own max-age; falls back so a malformed header cannot disable caching. */
    private function cacheTtlFrom(?string $cacheControl): int
    {
        $fallback = (int) config('apiauth.firebase.certs_fallback_ttl', 3600);

        if ($cacheControl !== null && preg_match('/max-age=(\d+)/i', $cacheControl, $matches)) {
            $maxAge = (int) $matches[1];

            if ($maxAge > 0) {
                return $maxAge;
            }
        }

        return $fallback;
    }

    private function projectId(): string
    {
        // Env first so a deploy can override, falling back to the settings row
        // the existing push-notification code already reads.
        $projectId = config('apiauth.firebase.project_id');

        if (!is_string($projectId) || $projectId === '') {
            $projectId = setting('firebase_project_id');
        }

        if (!is_string($projectId) || $projectId === '') {
            throw new RuntimeException(
                'No Firebase project id configured. Set FIREBASE_PROJECT_ID in .env or firebase_project_id in Settings.'
            );
        }

        return $projectId;
    }
}
