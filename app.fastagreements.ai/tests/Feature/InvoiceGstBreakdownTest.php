<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Setting;
use App\Models\State;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Support\GstBreakdown;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * The point of the invoice change: whatever the tax split, the rendered
 * summary must add back up to the amount the customer actually paid.
 *
 * The models are built in memory with their relations wired by hand — the
 * view only reads attributes, so nothing here needs to touch those tables.
 * Settings do hit the database, hence DatabaseTransactions.
 */
class InvoiceGstBreakdownTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('company_state', 'Gujarat');
        Setting::set('gst_percentage', '18');
    }

    private function renderInvoice(float $amount, ?string $stateName): string
    {
        $customer = new Customer([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'mobile' => '9999999999',
        ]);
        $customer->setRelation('state', $stateName ? (new State)->forceFill(['name' => $stateName]) : null);

        $invoice = new SubscriptionInvoice([
            'invoice_number' => 'TEST-1',
            'amount' => $amount,
            'invoice_date' => now(),
            'payment_status' => 'paid',
            'payment_method' => 'online',
        ]);
        $invoice->setRelation('customer', $customer);
        $invoice->setRelation('subscriptionPlan', new SubscriptionPlan(['name' => 'Pay Per Agreement']));

        return View::make('admin.subscription_invoices.pdf', compact('invoice'))->render();
    }

    public function test_an_intra_state_invoice_shows_cgst_and_sgst_that_sum_to_the_amount(): void
    {
        $html = $this->renderInvoice(15.00, 'Gujarat');
        $gst = new GstBreakdown(15.00, true, 18);

        $this->assertStringContainsString('CGST (9%)', $html);
        $this->assertStringContainsString('SGST (9%)', $html);
        $this->assertStringNotContainsString('IGST', $html);

        $this->assertStringContainsString('₹' . number_format($gst->taxable, 2), $html);
        $this->assertStringContainsString('₹' . number_format($gst->cgst, 2), $html);
        $this->assertStringContainsString('₹15.00', $html);

        $this->assertSame(15.00, round($gst->taxable + $gst->cgst + $gst->sgst, 2));
    }

    public function test_an_inter_state_invoice_shows_igst_instead(): void
    {
        $html = $this->renderInvoice(15.00, 'Maharashtra');

        $this->assertStringContainsString('IGST (18%)', $html);
        $this->assertStringNotContainsString('CGST', $html);
        $this->assertStringContainsString('Place of Supply : Maharashtra', $html);
    }

    public function test_a_customer_with_no_state_is_billed_intra_state(): void
    {
        $html = $this->renderInvoice(15.00, null);

        $this->assertStringContainsString('CGST (9%)', $html);
        $this->assertStringNotContainsString('IGST', $html);
    }

    public function test_the_invoice_no_longer_shows_a_hardcoded_zero_tax(): void
    {
        $html = $this->renderInvoice(399.00, 'Gujarat');
        $gst = new GstBreakdown(399.00, true, 18);

        $this->assertGreaterThan(0, $gst->cgst);
        $this->assertStringContainsString('₹' . number_format($gst->cgst, 2), $html);
        $this->assertStringContainsString('₹399.00', $html);
    }

    public function test_the_rate_comes_from_settings(): void
    {
        Setting::set('gst_percentage', '5');

        $html = $this->renderInvoice(105.00, 'Gujarat');

        $this->assertStringContainsString('CGST (2.5%)', $html);
        $this->assertStringContainsString('₹100.00', $html);
        $this->assertStringContainsString('₹105.00', $html);
    }
}
