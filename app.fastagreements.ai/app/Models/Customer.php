<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\City;
use App\Models\State;
use App\Models\Country;

/**
 * Authenticatable so the mobile API can resolve the caller from a signed token
 * rather than from a `customer_id` sent in the request body.
 *
 * There is deliberately no password: customers prove who they are through
 * Firebase phone verification, and this model is never used with a password
 * broker or the session guard.
 */
class Customer extends Model implements AuthenticatableContract
{
    use HasFactory, AuthenticatableTrait;

    protected $appends = [
        'person_image_url',
        'upi_image_url',
        'total_agreements',
        'city_name',
        'state_name',
        'country_name',
    ]; 

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'address',
        'city_id',
        'state_id',
        'country_id',
        'per_address',
        'per_city_id',
        'per_state_id',
        'per_country_id',
        'person_image',
        'aadhaar_card',
        'is_aadhaar_verify',
        'aadhaar_card_all_column',
        'is_active',
        'is_payment_details_configured',
        'bank_name',
        'account_number',
        'ifsc',
        'account_type',
        'upi_id',
        'upi_image',
        'last_lat',
        'last_lon',
        'location',
        'signature',
        'photo',
        'occupation',
       'date_of_birth',
      'gender',
      'fcm_token'
    ];

    // protected $casts = [
    //     'is_aadhaar_verify' => 'boolean',
    //     'aadhaar_card_all_column' => 'array',
    //     'is_active' => 'boolean',
    //     'is_payment_details_configured' => 'boolean',
    //     'last_lat' => 'decimal:10,7',
    //     'last_lon' => 'decimal:10,7',
    // ];

   // Accessor for person_image_url
   public function getPersonImageUrlAttribute()
   {
       return $this->person_image ?  asset('admin/images/person_images_thumb/' . $this->person_image) : null;
   }
   public function getUpiImageUrlAttribute()
   {
       return $this->person_image ? asset('admin/images/upi_images_thumb/' . $this->upi_image) : null;
   }

    protected $casts = [
        'is_aadhaar_verify' => 'boolean',
        'aadhaar_card_all_column' => 'array',
        'is_active' => 'boolean',
        'is_payment_details_configured' => 'boolean',
        'last_lat' => 'decimal:7', // Only specify the scale
        'last_lon' => 'decimal:7', // Only specify the scale
    ];
    

  public function activeSubscription()
    {
        return $this->hasOne(CustomerSubscription::class)
            ->where('is_active', 1)
            ->orderBy('end_date', 'desc');
    }

     public function subscriptionStatus()
    {
        $today = Carbon::today();

        $subscription = $this->activeSubscription()->first();

        if (!$subscription) {
            return [
                'status' => 'inactive',
                'message' => 'No active subscription found',
            ];
        }

        /**
         * Per-agreement plan
         */
        if (!is_null($subscription->remaining_agreements)) {

            if ($subscription->remaining_agreements <= 0) {
                return [
                    'status' => 'expired',
                    'message' => 'Agreement limit exhausted',
                    'remaining_agreements' => 0,
                ];
            }

            return [
                'status' => 'active',
                'message' => 'Per agreement plan active',
                'remaining_agreements' => $subscription->remaining_agreements,
            ];
        }

        $start = Carbon::parse($subscription->start_date);
        $end   = $subscription->end_date ? Carbon::parse($subscription->end_date) : null;

        /**
         * Lifetime plan
         */
        if (is_null($end)) {
            return [
                'status' => 'active',
                'days_remaining' => null,
                'message' => 'Lifetime subscription active',
                'start_date' => $start->toDateString(),
                'end_date' => null,
                'is_expiring_soon' => false,
            ];
        }

        /**
         * Expired
         */
        if ($today->gt($end)) {
            return [
                'status' => 'expired',
                'days_remaining' => 0,
                'message' => 'Your subscription has expired',
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'is_expiring_soon' => false,
            ];
        }

        /**
         * Not started yet
         */
        if ($today->lt($start)) {
            return [
                'status' => 'inactive',
                'days_remaining' => $today->diffInDays($start),
                'message' => 'Subscription not started yet',
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'is_expiring_soon' => false,
            ];
        }

        /**
         * Active
         */
        $daysRemaining = $today->diffInDays($end);
        $isExpiringSoon = $daysRemaining <= 7;

        return [
            'status' => 'active',
            'days_remaining' => $daysRemaining,
            'message' => $isExpiringSoon
                ? "Your subscription will expire in {$daysRemaining} days"
                : 'Your subscription is active',
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_expiring_soon' => $isExpiringSoon,
        ];
    }

    public function party1Agreements()
    {
        return $this->hasMany(Aggriment::class, 'party_1_id');
    }

    public function party2Agreements()
    {
        return $this->hasMany(Aggriment::class, 'party_2_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function getCityNameAttribute()
    {
        return $this->city ? $this->city->city : 'N/A';
    }

    public function getStateNameAttribute()
    {
        return $this->state ? $this->state->name : 'N/A';
    }

    public function getCountryNameAttribute()
    {
        return $this->country ? $this->country->name : 'N/A';
    }

    public function getTotalAgreementsAttribute()
    {
        return $this->party1Agreements()->count() + $this->party2Agreements()->count();
    }
}
