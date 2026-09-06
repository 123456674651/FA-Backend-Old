<?php

namespace Tests\Unit;

use App\Services\Auth\Msg91TokenVerifier;
use App\Services\Auth\Msg91UnavailableException;
use App\Services\Auth\PhoneVerificationException;
use App\Support\MobileNumber;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class Msg91TokenVerifierTest extends TestCase
{
    private const URL = 'https://control.msg91.com/api/v5/widget/verifyAccessToken';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('apiauth.msg91.auth_key', 'test-key');
        config()->set('apiauth.msg91.verify_url', self::URL);
    }

    private function verifier(): Msg91TokenVerifier
    {
        return app(Msg91TokenVerifier::class);
    }

    public function test_a_successful_response_yields_the_verified_number(): void
    {
        Http::fake([self::URL => Http::response([
            'message' => '919876543210',
            'type' => 'success',
        ])]);

        $identity = $this->verifier()->verify('a-token');

        $this->assertSame('919876543210', $identity['phone_number']);
        $this->assertNotSame('', $identity['uid']);
    }

    /**
     * The success envelope's exact formatting is still unobserved (see the
     * task brief), and MobileNumber exists specifically to absorb numbers
     * written with spaces and hyphens. The identifier check must reject on
     * content (a sentence, an email), never on formatting like this — or the
     * first real verification MSG91 ever answers could turn into a false,
     * logged outage.
     */
    #[DataProvider('separatorFormattedIdentifiers')]
    public function test_separator_formatted_identifiers_are_accepted(string $identifier): void
    {
        Http::fake([self::URL => Http::response(['message' => $identifier, 'type' => 'success'])]);

        $identity = $this->verifier()->verify('a-token');

        $this->assertSame('9876543210', MobileNumber::toStored($identity['phone_number']));
    }

    /** @return array<string, array{0: string}> */
    public static function separatorFormattedIdentifiers(): array
    {
        return [
            'a plus and a space' => ['+91 9876543210'],
            'a hyphen' => ['91-9876543210'],
        ];
    }

    /**
     * `uid` feeds Task 7's replay guard, which is keyed on it behind a UNIQUE
     * index that is never pruned. If `uid` were the phone number, a
     * customer's second legitimate verification of their own number would
     * collide with their first and be rejected as a replay, forever.
     */
    public function test_uid_is_per_verification_not_per_number(): void
    {
        Http::fake([self::URL => Http::response(['message' => '919876543210', 'type' => 'success'])]);

        $first = $this->verifier()->verify('token-one');
        $second = $this->verifier()->verify('token-two');

        // Same number, different tokens: the replay-guard key must differ, or a
        // customer's second legitimate verification of their own number would be
        // rejected as a replay.
        $this->assertSame($first['phone_number'], $second['phone_number']);
        $this->assertNotSame($first['uid'], $second['uid']);
        $this->assertStringNotContainsString('9876543210', $first['uid']);
    }

    /**
     * Determinism is what makes the replay guard work at all: re-presenting
     * one token must collide on provider_ref. Uniqueness alone (the test
     * above) would also pass for a random or salted uid, which would let
     * every replay through silently — this is the other half of the contract.
     */
    public function test_the_same_token_always_yields_the_same_uid(): void
    {
        Http::fake([self::URL => Http::response(['message' => '919876543210', 'type' => 'success'])]);

        $first = $this->verifier()->verify('the-same-token');
        $second = $this->verifier()->verify('the-same-token');

        $this->assertSame($first['uid'], $second['uid']);
    }

    public function test_it_sends_the_key_in_the_body_under_the_hyphenated_field(): void
    {
        Http::fake([self::URL => Http::response(['message' => '919876543210', 'type' => 'success'])]);

        $this->verifier()->verify('a-token');

        Http::assertSent(function ($request) {
            return $request->url() === self::URL
                && $request['authkey'] === 'test-key'
                && $request['access-token'] === 'a-token';
        });
    }

    /**
     * MSG91 answers HTTP 200 for a rejected token (probed 2026-09-06), so a
     * verifier that trusts the status code would treat this as a success.
     */
    public function test_an_error_response_is_rejected_despite_http_200(): void
    {
        Http::fake([self::URL => Http::response([
            'message' => 'AuthenticationFailure',
            'type' => 'error',
            'code' => '418',
        ], 200)]);

        $this->expectException(PhoneVerificationException::class);
        $this->verifier()->verify('bad');
    }

    /**
     * A rejected authkey is our outage, not the caller's bad code. It must not
     * surface as a 401 telling the customer their code was wrong — that sends
     * everyone chasing the wrong problem while sign-in is down for everybody.
     */
    public function test_a_rejected_authkey_escalates_rather_than_blaming_the_caller(): void
    {
        Http::fake([self::URL => Http::response([
            'message' => 'AuthenticationFailure',
            'type' => 'error',
            'code' => '201',
        ], 200)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('misconfigured');

        try {
            $this->verifier()->verify('a-token');
        } catch (PhoneVerificationException $e) {
            $this->fail('A bad authkey must not be reported as an invalid token.');
        }
    }

    public function test_a_success_with_no_identifier_fails_hard(): void
    {
        Http::fake([self::URL => Http::response(['type' => 'success'])]);

        $this->expectException(PhoneVerificationException::class);
        $this->verifier()->verify('a-token');
    }

    #[DataProvider('nonPhoneIdentifiers')]
    public function test_a_success_whose_identifier_is_not_a_phone_number_fails_hard(string $identifier): void
    {
        Http::fake([self::URL => Http::response([
            'message' => $identifier,
            'type' => 'success',
        ])]);

        $this->expectException(PhoneVerificationException::class);
        $this->verifier()->verify('a-token');
    }

    /** @return array<string, array{0: string}> */
    public static function nonPhoneIdentifiers(): array
    {
        return [
            'an email address' => ['someone@example.com'],
            // MobileNumber::toStored() takes the last ten digits of whatever
            // it is given, so a sentence that merely contains a number must
            // not be accepted as a verified one.
            'a sentence containing a number' => ['Your code 9876543210 is verified on 2026-09-06'],
        ];
    }

    /**
     * An MSG91 outage is our fault, not the caller's bad code. It must not
     * render the same way an actually-invalid token does — that tells every
     * customer their code is wrong while the logs stay silent about the real
     * cause. bootstrap/app.php maps this type to 503 VERIFICATION_UNAVAILABLE
     * ahead of the generic 401 PhoneVerificationException handler.
     */
    public function test_an_http_failure_surfaces_as_unavailable_not_invalid_token(): void
    {
        Http::fake([self::URL => Http::response('gateway down', 502)]);

        $this->expectException(Msg91UnavailableException::class);
        $this->verifier()->verify('a-token');
    }

    /** Untested early return in security code otherwise: a missing key must not silently pass through. */
    public function test_a_missing_auth_key_throws_rather_than_calling_out(): void
    {
        config()->set('apiauth.msg91.auth_key', '');

        Http::fake([self::URL => Http::response(['message' => '919876543210', 'type' => 'success'])]);

        $this->expectException(RuntimeException::class);

        try {
            $this->verifier()->verify('a-token');
        } finally {
            Http::assertNothingSent();
        }
    }
}
