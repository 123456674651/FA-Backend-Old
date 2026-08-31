<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An intent to pay, recorded before the customer reaches the gateway.
 *
 * The amount here is the only one the system believes. The client names a
 * plan; the server prices it and writes that price both to this row and to
 * Razorpay, so a tampered request can only ever buy the thing it named at
 * the price the admin set.
 */
class PaymentOrder extends Model
{
    use HasFactory;

    public const STATUS_CREATED = 'created';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'customer_id',
        'subscription_plan_id',
        'status',
        'amount_paise',
        'currency',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'failure_reason',
        'fulfilled_at',
        'expires_at',
    ];

    protected $casts = [
        'amount_paise' => 'integer',
        'fulfilled_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_CREATED,
        'currency' => 'INR',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * A paid order is never expired, however old. Expiry describes an intent
     * that was abandoned, not a payment that succeeded late.
     */
    public function isExpired(): bool
    {
        if ($this->isPaid()) {
            return false;
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
