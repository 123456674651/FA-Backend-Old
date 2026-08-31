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
}
