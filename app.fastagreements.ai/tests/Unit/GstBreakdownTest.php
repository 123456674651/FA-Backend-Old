<?php

namespace Tests\Unit;

use App\Support\GstBreakdown;
use Tests\TestCase;

/**
 * The rate is passed explicitly throughout so these stay pure — no settings
 * table, no database. The settings-backed default is covered separately.
 */
class GstBreakdownTest extends TestCase
{
    public function test_intra_state_components_sum_back_to_the_inclusive_amount(): void
    {
        $gst = new GstBreakdown(15.00, true, 18);

        $this->assertSame(12.70, $gst->taxable);
        $this->assertSame(1.15, $gst->cgst);
        $this->assertSame(1.15, $gst->sgst);
        $this->assertSame(0.0, $gst->igst);
        $this->assertSame(15.00, round($gst->taxable + $gst->cgst + $gst->sgst, 2));
    }

    public function test_inter_state_puts_the_whole_tax_in_igst(): void
    {
        $gst = new GstBreakdown(15.00, false, 18);

        $this->assertSame(0.0, $gst->cgst);
        $this->assertSame(0.0, $gst->sgst);
        $this->assertSame(2.29, $gst->igst);
        $this->assertSame(12.71, $gst->taxable);
        $this->assertSame(15.00, round($gst->taxable + $gst->igst, 2));
    }

    /**
     * The awkward amounts — the ones where dividing instead of subtracting
     * would leave the invoice column a paisa off the total.
     */
    public function test_components_always_sum_to_the_amount(): void
    {
        foreach ([0.01, 1, 15, 99.99, 399, 10300, 12345.67] as $amount) {
            foreach ([true, false] as $intra) {
                $gst = new GstBreakdown((float) $amount, $intra, 18);

                $this->assertSame(
                    round((float) $amount, 2),
                    round($gst->taxable + $gst->cgst + $gst->sgst + $gst->igst, 2),
                    "components did not sum back for {$amount}"
                );
            }
        }
    }

    public function test_a_zero_amount_produces_no_tax(): void
    {
        $gst = new GstBreakdown(0.0, true, 18);

        $this->assertSame(0.0, $gst->taxable);
        $this->assertSame(0.0, $gst->totalTax());
    }

    public function test_a_zero_rate_leaves_the_whole_amount_taxable(): void
    {
        $gst = new GstBreakdown(15.00, true, 0);

        $this->assertSame(15.00, $gst->taxable);
        $this->assertSame(0.0, $gst->totalTax());
    }

    public function test_a_non_default_rate_is_honoured(): void
    {
        $gst = new GstBreakdown(105.00, true, 5);

        $this->assertSame(5.0, $gst->totalTax());
        $this->assertSame(100.00, $gst->taxable);
        $this->assertSame(2.5, $gst->halfRate());
    }

    public function test_a_customer_in_the_company_state_is_intra_state(): void
    {
        $this->assertTrue(GstBreakdown::isIntraState('Gujarat'));
        $this->assertTrue(GstBreakdown::isIntraState('  gujarat '));
    }

    public function test_a_customer_in_another_state_is_inter_state(): void
    {
        $this->assertFalse(GstBreakdown::isIntraState('Maharashtra'));
    }

    public function test_a_customer_with_no_state_falls_back_to_intra_state(): void
    {
        $this->assertTrue(GstBreakdown::isIntraState(null));
        $this->assertTrue(GstBreakdown::isIntraState(''));
        $this->assertTrue(GstBreakdown::isIntraState('   '));
    }

    public function test_rates_format_without_trailing_zeros(): void
    {
        $this->assertSame('18', GstBreakdown::formatRate(18.0));
        $this->assertSame('9', GstBreakdown::formatRate(9.0));
        $this->assertSame('2.5', GstBreakdown::formatRate(2.5));
    }
}
