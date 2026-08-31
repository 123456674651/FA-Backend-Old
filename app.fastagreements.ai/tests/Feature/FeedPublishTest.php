<?php

namespace Tests\Feature;

use App\Services\Auth\JwtService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Publishing an agreement to the feed is an explicit, opt-in action taken
 * after payment — creating an agreement no longer posts one by itself.
 *
 * DatabaseTransactions rather than RefreshDatabase: this schema was imported
 * from a dump, not built by the 23 migrations in database/migrations, so
 * migrate:fresh cannot rebuild it.
 */
class FeedPublishTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCustomer(string $name): int
    {
        return DB::table('customers')->insertGetId([
            'name' => $name,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** feeds.category_id is an FK to deal_categories, so the row must exist. */
    private function makeCategory(string $name = 'Test Category'): int
    {
        return DB::table('deal_categories')->insertGetId([
            'category_name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeAgreement(int $party1, int $party2, ?int $categoryId = null): int
    {
        return DB::table('agreements')->insertGetId([
            'party_1_id' => $party1,
            'party_2_id' => $party2,
            'category_id' => $categoryId ?? $this->makeCategory(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function tokenFor(int $customerId): string
    {
        return app(JwtService::class)->issueForCustomer($customerId);
    }

    private function publish(int $callerId, array $body): \Illuminate\Testing\TestResponse
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->tokenFor($callerId))
            ->postJson('/api/feed/publish', $body);
    }

    public function test_creator_publishing_an_agreement_creates_one_feed_row(): void
    {
        $creator = $this->makeCustomer('Party One');
        $other = $this->makeCustomer('Party Two');
        $categoryId = $this->makeCategory('Vehicle Sale');
        $agreementId = $this->makeAgreement($creator, $other, $categoryId);

        $response = $this->publish($creator, ['agreement_id' => $agreementId]);

        $response->assertStatus(200)->assertJsonPath('status', true);

        $this->assertDatabaseHas('feeds', [
            'agreement_id' => $agreementId,
            'type' => 'agreement_created',
            'customer_id' => $creator,
            'customer_id2' => $other,
            'category_id' => $categoryId,
        ]);
    }

    public function test_unknown_agreement_is_rejected(): void
    {
        $caller = $this->makeCustomer('Nobody');

        $response = $this->publish($caller, ['agreement_id' => 99999999]);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('feeds', ['agreement_id' => 99999999]);
    }

    public function test_a_customer_who_is_not_the_creator_cannot_publish(): void
    {
        $creator = $this->makeCustomer('Party One');
        $other = $this->makeCustomer('Party Two');
        $agreementId = $this->makeAgreement($creator, $other);

        $response = $this->publish($other, ['agreement_id' => $agreementId]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('feeds', ['agreement_id' => $agreementId]);
    }

    public function test_publishing_twice_does_not_create_a_second_feed_row(): void
    {
        $creator = $this->makeCustomer('Party One');
        $other = $this->makeCustomer('Party Two');
        $agreementId = $this->makeAgreement($creator, $other);

        $this->publish($creator, ['agreement_id' => $agreementId])->assertStatus(200);
        $this->publish($creator, ['agreement_id' => $agreementId])->assertStatus(200);

        $this->assertSame(1, DB::table('feeds')->where('agreement_id', $agreementId)->count());
    }

    public function test_agreement_id_is_required(): void
    {
        $caller = $this->makeCustomer('Party One');

        $this->publish($caller, [])->assertStatus(422);
    }

    public function test_an_unauthenticated_caller_is_rejected(): void
    {
        $creator = $this->makeCustomer('Party One');
        $agreementId = $this->makeAgreement($creator, $this->makeCustomer('Party Two'));

        $this->postJson('/api/feed/publish', ['agreement_id' => $agreementId])
            ->assertStatus(401);

        $this->assertDatabaseMissing('feeds', ['agreement_id' => $agreementId]);
    }
}
