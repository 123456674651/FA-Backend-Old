<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    public const OTP_WITH = 'with_otp';
    public const OTP_WITHOUT = 'without_otp';

    protected $fillable = [
        'name',
        'price',
        'duration_type',
        'duration_value',
        'otp_mode',
        'agreement_limit',
        'features',
        'is_active'
    ];

    protected $casts = [
        'features' => 'array'
    ];

    public function calculateEndDate($startDate)
    {
        return match ($this->duration_type) {
            'daily'   => $startDate->copy()->addDays($this->duration_value),
            'monthly' => $startDate->copy()->addMonths($this->duration_value),
            'yearly'  => $startDate->copy()->addYears($this->duration_value),
            'lifetime'  => null,
            'per_agreement' => null,

            default => throw new \Exception('Invalid duration type'),
        };
    }

    /**
     * The plan price as integer paise.
     *
     * Rounded once, here, so no caller repeats the float-to-int conversion
     * and gets a different answer.
     */
    public function amountPaise(): int
    {
        return (int) round(((float) $this->price) * 100);
    }

    public function isPerAgreement(): bool
    {
        return $this->normalisedDuration() === 'per_agreement';
    }

    /**
     * When this plan stops covering the customer. Null means "no end date" —
     * lifetime plans and quota plans, which are bounded by a counter instead.
     *
     * duration_type is spelled inconsistently across the codebase and the
     * data (days/daily, months/monthly, years/yearly), so every spelling is
     * accepted here and nowhere else.
     */
    public function resolveEndDate(\Carbon\Carbon $start): ?\Carbon\Carbon
    {
        return match ($this->normalisedDuration()) {
            'per_agreement', 'lifetime' => null,
            'days'   => $start->copy()->addDays((int) $this->duration_value),
            'months' => $start->copy()->addMonths((int) $this->duration_value),
            'years'  => $start->copy()->addYears((int) $this->duration_value),
            default  => throw new \InvalidArgumentException(
                "Unknown subscription duration_type [{$this->duration_type}]."
            ),
        };
    }

    /**
     * Throwing on an unrecognised type is deliberate. Falling through to
     * "no end date" would hand out an accidental lifetime plan, and that is
     * a worse failure than a loud one.
     */
    private function normalisedDuration(): string
    {
        return match ((string) $this->duration_type) {
            'per_agreement' => 'per_agreement',
            'lifetime' => 'lifetime',
            'day', 'days', 'daily' => 'days',
            'month', 'months', 'monthly' => 'months',
            'year', 'years', 'yearly' => 'years',
            default => (string) $this->duration_type,
        };
    }
}
