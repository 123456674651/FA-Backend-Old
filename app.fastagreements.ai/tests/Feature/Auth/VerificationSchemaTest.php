<?php

namespace Tests\Feature\Auth;

use App\Models\AgreementPartyVerification;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VerificationSchemaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_party_phone_verifications_carries_a_provider_ref(): void
    {
        $this->assertTrue(Schema::hasColumn('party_phone_verifications', 'provider_ref'));
    }

    public function test_agreement_party_verifications_carries_a_provider_ref(): void
    {
        $this->assertTrue(Schema::hasColumn('agreement_party_verifications', 'provider_ref'));
    }

    public function test_firebase_uid_is_kept_for_historical_rows(): void
    {
        $this->assertTrue(Schema::hasColumn('party_phone_verifications', 'firebase_uid'));
        $this->assertTrue(Schema::hasColumn('agreement_party_verifications', 'firebase_uid'));
    }

    public function test_msg91_is_a_recognised_verification_source(): void
    {
        $this->assertSame('msg91', AgreementPartyVerification::VIA_MSG91);
    }

    public function test_used_tokens_have_their_own_append_only_table(): void
    {
        $this->assertTrue(Schema::hasTable('used_phone_tokens'));
        $this->assertTrue(Schema::hasColumn('used_phone_tokens', 'provider_ref'));
    }

    public function test_the_same_token_cannot_be_recorded_twice(): void
    {
        DB::table('used_phone_tokens')->insert([
            'provider_ref' => 'dup-ref-test',
            'used_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('used_phone_tokens')->insert([
            'provider_ref' => 'dup-ref-test',
            'used_at' => now(),
        ]);
    }
}
