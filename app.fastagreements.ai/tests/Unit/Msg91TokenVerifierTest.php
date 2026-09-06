<?php

namespace Tests\Unit;

use App\Services\Auth\Msg91TokenVerifier;
use App\Services\Auth\PhoneVerificationException;
use Illuminate\Support\Facades\Http;
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

    public function test_a_success_whose_identifier_is_not_a_phone_number_fails_hard(): void
    {
        Http::fake([self::URL => Http::response([
            'message' => 'someone@example.com',
            'type' => 'success',
        ])]);

        $this->expectException(PhoneVerificationException::class);
        $this->verifier()->verify('a-token');
    }

    public function test_an_http_failure_is_rejected_not_swallowed(): void
    {
        Http::fake([self::URL => Http::response('gateway down', 502)]);

        $this->expectException(PhoneVerificationException::class);
        $this->verifier()->verify('a-token');
    }
}
