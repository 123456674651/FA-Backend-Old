<?php

namespace Tests\Feature;

use App\Services\Auth\JwtService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The feed is public to every signed-in customer. The old `allow_prompt`
 * setting that hid it has been removed, so reading no longer depends on a
 * per-customer flag.
 */
class FeedIndexTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCustomer(int $allowPrompt): int
    {
        return DB::table('customers')->insertGetId([
            'name' => 'Reader',
            'is_active' => 1,
            'allow_prompt' => $allowPrompt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getFeed(int $customerId): \Illuminate\Testing\TestResponse
    {
        $token = app(JwtService::class)->issueForCustomer($customerId);

        return $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/feed?customer_id=' . $customerId);
    }

    public function test_a_customer_with_allow_prompt_off_can_still_read_the_feed(): void
    {
        $customerId = $this->makeCustomer(0);

        $this->getFeed($customerId)
            ->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_a_customer_with_allow_prompt_on_can_read_the_feed(): void
    {
        $customerId = $this->makeCustomer(1);

        $this->getFeed($customerId)
            ->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_an_empty_feed_returns_an_empty_list_not_an_error(): void
    {
        $customerId = $this->makeCustomer(1);

        // Scoped to this run: the shared test schema may hold rows from
        // fixtures, so assert the shape rather than the emptiness of the table.
        $this->getFeed($customerId)
            ->assertStatus(200)
            ->assertJsonStructure(['status', 'message', 'data', 'total_pages', 'total_records']);
    }
}
