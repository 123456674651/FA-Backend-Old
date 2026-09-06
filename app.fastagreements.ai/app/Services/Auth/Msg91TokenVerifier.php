<?php

namespace App\Services\Auth;

use App\Support\MobileNumber;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Verifies MSG91 OTP widget access tokens.
 *
 * The widget sends and checks the code on the handset, so — as with the
 * Firebase verifier this replaces — the server never sees the code. What it
 * can do is refuse to take the app's word for the outcome: MSG91 will only
 * confirm a token it issued, and the number it returns is the one it actually
 * verified.
 *
 * Unlike a Firebase ID token there is no signature to check locally; the proof
 * is that MSG91 recognises the token, which costs one round trip. The auth key
 * travels in the body, so this call can only ever be made server-side.
 */
class Msg91TokenVerifier implements PhoneIdentityVerifier
{
    /** MSG91's code for "your authkey was rejected" — our problem, not the caller's. */
    private const CODE_BAD_AUTHKEY = '201';

    /**
     * `uid` is a digest of the *token being spent*, not the verified number.
     * It is the replay-guard key (Task 7's `used_phone_tokens.provider_ref`,
     * behind a UNIQUE index that is never pruned) and must be unique per
     * verification, not per phone number — otherwise a customer's second
     * legitimate verification of their own number would collide with their
     * first, forever, and one customer confirming someone else's number
     * (e.g. a guarantor's) would lock that number out for everyone. Storing
     * a digest rather than the live token also means nothing spendable sits
     * in that table.
     *
     * @return array{uid: string, phone_number: string}
     *
     * @throws Msg91TokenException
     * @throws Msg91UnavailableException
     */
    public function verify(string $token): array
    {
        $response = $this->ask($token);

        if (($response['type'] ?? null) !== 'success') {
            // A rejected authkey is a server misconfiguration — an expired key,
            // a wrong one, or an IP-security block — and it fails EVERY request,
            // not just this caller's. Reporting it as "your code is invalid"
            // would have every customer and every engineer chasing the wrong
            // problem during a total outage. So it escalates rather than 401s.
            if ((string) ($response['code'] ?? '') === self::CODE_BAD_AUTHKEY) {
                Log::critical('MSG91 rejected our authkey — phone verification is down: ' . json_encode($response));

                throw new RuntimeException('Phone verification is misconfigured.');
            }

            // MSG91 puts its reason in `message` on failure. Not surfaced to the
            // client verbatim — it is written for us, not for a customer.
            Log::info('MSG91 rejected an access token: ' . json_encode($response));

            throw new Msg91TokenException('The phone verification token is invalid or has expired.');
        }

        $identifier = trim((string) ($response['message'] ?? ''));

        // Must BE a number, not merely contain one: MobileNumber::toStored()
        // takes the last ten digits of whatever it is given, so a sentence
        // ("Your number 9876543210 was verified on...") or a numeric MSG91
        // request id would otherwise be silently accepted as a verified
        // mobile and used to auto-provision a customer at a fabricated number.
        if (!preg_match('/^\+?\d{10,15}$/', $identifier)) {
            Log::error('MSG91 verified a token but returned no usable number: ' . json_encode($response));

            throw new Msg91TokenException('That verification did not confirm a phone number.');
        }

        $mobile = MobileNumber::toStored($identifier);

        // A success carrying no usable number is not something to work around.
        // Falling back to anything the client sent would quietly reduce this
        // whole mechanism to "the app said so", which is the one thing it
        // exists to prevent.
        if (strlen($mobile) !== 10) {
            Log::error('MSG91 verified a token but returned no usable number: ' . json_encode($response));

            throw new Msg91TokenException('That verification did not confirm a phone number.');
        }

        return ['uid' => hash('sha256', $token), 'phone_number' => $identifier];
    }

    /** @return array<string, mixed> */
    private function ask(string $token): array
    {
        $authKey = config('apiauth.msg91.auth_key');

        if (!is_string($authKey) || $authKey === '') {
            throw new RuntimeException('No MSG91 auth key configured. Set MSG91_AUTH_KEY in .env.');
        }

        // A non-numeric MSG91_TIMEOUT_SECONDS casts to 0, and Guzzle reads
        // timeout => 0 as "no timeout" — a typo would pin PHP-FPM workers
        // against a hung MSG91 indefinitely instead of just being slow.
        $timeout = max(1, (int) config('apiauth.msg91.timeout', 10));

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->post((string) config('apiauth.msg91.verify_url'), [
                    'authkey' => $authKey,
                    // Hyphenated, per MSG91's API. Not a typo.
                    'access-token' => $token,
                ]);
        } catch (ConnectionException $e) {
            // Our outage, not the caller's bad code — never the request body,
            // which carries the auth key.
            Log::error('MSG91 was unreachable while verifying a token: ' . $e->getMessage());

            throw new Msg91UnavailableException('Could not reach MSG91 to verify the number. Please try again.');
        }

        // Note this is NOT the success test. MSG91 answers 200 for a rejected
        // token and a rejected authkey alike (probed 2026-09-06) — only `type`
        // in the body distinguishes them. This catches transport failures and
        // 5xx, nothing more.
        if (!$response->successful()) {
            Log::error('MSG91 answered with a transport failure while verifying a token: HTTP ' . $response->status());

            throw new Msg91UnavailableException('Could not reach MSG91 to verify the number. Please try again.');
        }

        $body = $response->json();

        return is_array($body) ? $body : [];
    }
}
