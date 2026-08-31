<?php

namespace Tests\Unit;

use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * No database and no DatabaseTransactions — these are pure model methods.
 * Tests\TestCase rather than PHPUnit's, so the framework is booted when
 * Eloquent constructs the model.
 */
class SubscriptionPlanTest extends TestCase
{
    private function plan(string $durationType, ?int $value = 1, float $price = 100): SubscriptionPlan
    {
        return new SubscriptionPlan([
            'name' => 'T',
            'price' => $price,
            'duration_type' => $durationType,
            'duration_value' => $value,
        ]);
    }

    public function test_price_converts_to_integer_paise(): void
    {
        $this->assertSame(39900, $this->plan('months', 1, 399.00)->amountPaise());
        $this->assertSame(1500, $this->plan('per_agreement', null, 15.00)->amountPaise());
    }

    public function test_fractional_rupees_round_to_nearest_paise(): void
    {
        $this->assertSame(1099, $this->plan('months', 1, 10.99)->amountPaise());
        $this->assertSame(2010, $this->plan('months', 1, 20.10)->amountPaise());
        // These specifically catch a naive (int) cast: the float product sits
        // just BELOW the integer paise value (e.g. 0.29 * 100 == 28.999999999999996),
        // so truncating instead of rounding silently loses a paise.
        $this->assertSame(29, $this->plan('months', 1, 0.29)->amountPaise());
        $this->assertSame(201, $this->plan('months', 1, 2.01)->amountPaise());
    }

    public function test_per_agreement_is_recognised(): void
    {
        $this->assertTrue($this->plan('per_agreement', null)->isPerAgreement());
        $this->assertFalse($this->plan('months', 1)->isPerAgreement());
    }

    /** Every spelling that exists anywhere in the codebase must resolve. */
    public static function durationProvider(): array
    {
        return [
            'days'    => ['days', 10, '2026-01-11'],
            'daily'   => ['daily', 10, '2026-01-11'],
            'months'  => ['months', 2, '2026-03-01'],
            'monthly' => ['monthly', 2, '2026-03-01'],
            'years'   => ['years', 1, '2027-01-01'],
            'yearly'  => ['yearly', 1, '2027-01-01'],
        ];
    }

    /**
     * The DB enum only permits per_agreement/monthly/yearly/lifetime, but the
     * codebase branches on days/months/years elsewhere, so every spelling
     * that could reach this method is covered.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('durationProvider')]
    public function test_it_resolves_every_duration_spelling(string $type, int $value, string $expected): void
    {
        $end = $this->plan($type, $value)->resolveEndDate(Carbon::parse('2026-01-01'));

        $this->assertSame($expected, $end->toDateString());
    }

    public function test_lifetime_and_per_agreement_have_no_end_date(): void
    {
        $this->assertNull($this->plan('lifetime', null)->resolveEndDate(Carbon::parse('2026-01-01')));
        $this->assertNull($this->plan('per_agreement', null)->resolveEndDate(Carbon::parse('2026-01-01')));
    }

    public function test_an_unknown_duration_type_throws_rather_than_granting_forever(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->plan('fortnightly', 1)->resolveEndDate(Carbon::parse('2026-01-01'));
    }
}
