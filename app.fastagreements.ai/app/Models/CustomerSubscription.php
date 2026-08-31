<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSubscription extends Model
{
    use HasFactory;

    protected $table = 'user_subscriptions';

    protected $fillable = [
        'customer_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'is_active',
        'remaining_agreements',
        'otp_mode',
        'payment_order_id'
    ];

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function invoice()
    {
        return $this->hasOne(SubscriptionInvoice::class, 'customer_subscription_id');
    }
}
