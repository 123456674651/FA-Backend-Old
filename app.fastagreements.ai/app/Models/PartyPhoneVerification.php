<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A phone number this customer has confirmed through Firebase, held until it
 * is spent on an agreement. See the migration for why verification happens
 * before the agreement rather than after.
 */
class PartyPhoneVerification extends Model
{
    use HasFactory;

    protected $table = 'party_phone_verifications';

    protected $fillable = [
        'customer_id',
        'mobile',
        'firebase_uid',
        'verified_at',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'verified_at' => 'datetime',
    ];
}
