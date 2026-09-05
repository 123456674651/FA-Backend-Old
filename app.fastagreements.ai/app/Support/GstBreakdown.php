<?php

namespace App\Support;

/**
 * Splits a GST-inclusive amount into its taxable value and tax components.
 *
 * Every price in this system — plan prices, invoice amounts — is stored
 * inclusive of GST. The customer pays the sticker price and nothing more, so
 * the tax is always back-calculated out of it, never added on top.
 *
 * The taxable value is derived by subtracting the (rounded) tax from the
 * amount rather than by dividing. Dividing rounds twice and leaves the
 * invoice column off by a paisa; subtracting guarantees
 * taxable + cgst + sgst + igst === amount, exactly, always.
 */
class GstBreakdown
{
    /** Services HSN/SAC code for the agreement service. */
    public const HSN_CODE = '9983';

    public readonly float $amount;
    public readonly float $rate;
    public readonly bool $isIntraState;
    public readonly float $taxable;
    public readonly float $cgst;
    public readonly float $sgst;
    public readonly float $igst;

    public function __construct(float $amount, bool $isIntraState, ?float $rate = null)
    {
        $this->amount = round($amount, 2);
        $this->rate = $rate ?? self::defaultRate();
        $this->isIntraState = $isIntraState;

        $tax = $this->rate > 0
            ? $this->amount - round($this->amount / (1 + ($this->rate / 100)), 2)
            : 0.0;

        if ($isIntraState) {
            $this->cgst = round($tax / 2, 2);
            $this->sgst = round($tax / 2, 2);
            $this->igst = 0.0;
        } else {
            $this->cgst = 0.0;
            $this->sgst = 0.0;
            $this->igst = round($tax, 2);
        }

        $this->taxable = round($this->amount - $this->cgst - $this->sgst - $this->igst, 2);
    }

    /**
     * Build the breakdown for an invoice, deciding intra vs inter-state from
     * the customer's state.
     *
     * A customer with no state on record is treated as intra-state: the
     * company is in Gujarat and that is where unidentified customers
     * overwhelmingly are, and CGST+SGST is the safer default to report.
     */
    public static function forCustomerState(float $amount, ?string $customerState, ?float $rate = null): self
    {
        return new self($amount, self::isIntraState($customerState), $rate);
    }

    public static function isIntraState(?string $customerState): bool
    {
        $customerState = trim(strtolower((string) $customerState));

        if ($customerState === '') {
            return true;
        }

        return $customerState === self::companyState();
    }

    public static function companyState(): string
    {
        return trim(strtolower(setting('company_state') ?: 'Gujarat'));
    }

    /** The configured GST rate as a percentage, e.g. 18. */
    public static function defaultRate(): float
    {
        $rate = setting('gst_percentage');

        return is_numeric($rate) ? (float) $rate : 18.0;
    }

    /** Half the rate — what a single CGST or SGST component is charged at. */
    public function halfRate(): float
    {
        return $this->rate / 2;
    }

    /** Total tax, however it is split. */
    public function totalTax(): float
    {
        return round($this->cgst + $this->sgst + $this->igst, 2);
    }

    /** Formats a rate for display without trailing zeros: 18, 9, 2.5. */
    public static function formatRate(float $rate): string
    {
        return rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');
    }
}
