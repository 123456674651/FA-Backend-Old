<?php

namespace Tests\Feature\Auth;

use App\Services\Auth\PhoneIdentityVerifier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Support\FakePhoneIdentityVerifier;
use Tests\TestCase;

class AuthExchangeTest extends TestCase
{
    use DatabaseTransactions;

    private FakePhoneIdentityVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->verifier = new FakePhoneIdentityVerifier();
        $this->app->instance(PhoneIdentityVerifier::class, $this->verifier);
    }

    private function endpoint(): string
    {
        return '/api/auth/firebase-exchange';
    }

    public function test_a_verified_number_issues_a_session_token(): void
    {
        $mobile = '9000000001';
        DB::table('customers')->insert([
            'name' => 'Existing', 'mobile' => $mobile, 'address' => '', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->verifier->willReturn('uid-1', '+91' . $mobile);

        $response = $this->postJson($this->endpoint(), ['id_token' => 'a-token']);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.is_new_customer', false);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertSame(['a-token'], $this->verifier->tokensSeen);
    }

    public function test_an_unknown_number_provisions_a_customer(): void
    {
        $mobile = '9000000002';
        $this->verifier->willReturn('uid-2', '+91' . $mobile);

        $this->postJson($this->endpoint(), ['id_token' => 't'])
            ->assertOk()
            ->assertJsonPath('data.is_new_customer', true)
            ->assertJsonPath('data.profile_complete', false);

        $this->assertDatabaseHas('customers', ['mobile' => $mobile]);
    }

    public function test_the_number_comes_from_the_provider_not_the_request(): void
    {
        $this->verifier->willReturn('uid-3', '+919000000003');

        $this->postJson($this->endpoint(), [
            'id_token' => 't',
            'mobile' => '9999999999',
        ])->assertOk();

        $this->assertDatabaseHas('customers', ['mobile' => '9000000003']);
        $this->assertDatabaseMissing('customers', ['mobile' => '9999999999']);
    }

    public function test_a_rejected_token_is_401_not_500(): void
    {
        $this->verifier->willThrow('nope');

        $this->postJson($this->endpoint(), ['id_token' => 'bad'])
            ->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    public function test_a_disabled_account_is_refused(): void
    {
        $mobile = '9000000004';
        DB::table('customers')->insert([
            'name' => 'Disabled', 'mobile' => $mobile, 'address' => '', 'is_active' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->verifier->willReturn('uid-4', '+91' . $mobile);

        $this->postJson($this->endpoint(), ['id_token' => 't'])
            ->assertStatus(403)
            ->assertJsonPath('code', 'ACCOUNT_DISABLED');
    }

    public function test_a_number_that_is_not_ten_digits_is_refused(): void
    {
        $this->verifier->willReturn('uid-5', '+4477009');

        $this->postJson($this->endpoint(), ['id_token' => 't'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'PHONE_UNSUPPORTED');
    }

    public function test_a_missing_token_is_rejected(): void
    {
        $this->postJson($this->endpoint(), [])->assertStatus(422);
    }
}
