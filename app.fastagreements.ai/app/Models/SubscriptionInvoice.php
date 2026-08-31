<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'subscription_plan_id',
        'customer_subscription_id',
        'agreement_category_id',
        'agreement_sub_category_id',
        'agreement_category_name',
        'agreement_sub_category_name',
        'agreement_id',
        'invoice_number',
        'amount',
        'invoice_date',
        'payment_status',
        'payment_method',
        'payment_order_id',
    ];

    protected $dates = [
        'invoice_date',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function agreement()
    {
        return $this->belongsTo(Aggriment::class, 'agreement_id', 'id');
    }
}
