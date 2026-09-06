<?php

namespace Tests\Feature\Auth;

use App\Services\Auth\JwtService;
use App\Services\Auth\PhoneIdentityVerifier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\Support\FakePhoneIdentityVerifier;
use Tests\TestCase;

class PartyVerificationTest extends TestCase
{
    use DatabaseTransactions;

    private FakePhoneIdentityVerifier $verifier;
    private int $customerId;
    private string $jwt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->verifier = new FakePhoneIdentityVerifier();
        $this->app->instance(PhoneIdentityVerifier::class, $this->verifier);

        $this->customerId = DB::table('customers')->insertGetId([
            'name' => 'Caller', 'mobile' => '9000001111', 'address' => '', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->jwt = app(JwtService::class)->issueForCustomer($this->customerId);
    }

    private function verify(string $token): TestResponse
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->jwt)
            ->postJson('/api/party-verifications/msg91', ['access_token' => $token]);
    }

    public function test_it_records_the_number_the_provider_verified(): void
    {
        $this->verifier->willReturn('req-1', '919888800001');

        $this->verify('token-1')
            ->assertOk()
            ->assertJsonPath('data.mobile', '9888800001')
            ->assertJsonPath('data.valid_for_minutes', 120);

        $this->assertDatabaseHas('party_phone_verifications', [
            'customer_id' => $this->customerId,
            'mobile' => '9888800001',
            'provider_ref' => 'req-1',
        ]);
    }

    public function test_a_token_cannot_be_verified_twice(): void
    {
        $this->verifier->willReturn('req-2', '919888800002');
        $this->verify('token-2')->assertOk();

        $this->verify('token-2')
            ->assertStatus(422)
            ->assertJsonPath('code', 'TOKEN_ALREADY_USED');
    }

    public function test_reconfirming_a_number_does_not_free_the_first_token(): void
    {
        $this->verifier->willReturn('req-3a', '919888800003');
        $this->verify('token-3a')->assertOk();

        $this->verifier->willReturn('req-3b', '919888800003');
        $this->verify('token-3b')->assertOk();

        // The row's provider_ref is now req-3b, but req-3a must still be spent.
        $this->verifier->willReturn('req-3a', '919888800003');
        $this->verify('token-3a')
            ->assertStatus(422)
            ->assertJsonPath('code', 'TOKEN_ALREADY_USED');
    }

    public function test_it_requires_a_session(): void
    {
        $this->postJson('/api/party-verifications/msg91', ['access_token' => 't'])
            ->assertStatus(401);
    }

    public function test_a_rejected_token_is_401(): void
    {
        $this->verifier->willThrow('nope');
        $this->verify('bad')->assertStatus(401);
    }

    public function test_the_retired_firebase_route_is_gone(): void
    {
        $this->withHeader('Authorization', 'Bearer ' . $this->jwt)
            ->postJson('/api/party-verifications/firebase', ['id_token' => 't'])
            ->assertStatus(404);
    }
}
