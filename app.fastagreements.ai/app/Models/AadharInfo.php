<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AadharInfo extends Model
{
    use HasFactory;

    protected $table = 'aadhar_respose';

    protected $fillable = [
        'user_id',
        'aadhaar_number',
        'status',
        'message',
        'email',
        'care_of',
        'name',
        'year_of_birth',
        'gender',
        'ref_id',
        'mobile_hash',
        'address',
        'dob',
        'photo_link',
        'house',
        'landmark',
        'pincode',
        'po',
        'state',
        'street',
        'subdist',
        'vtc',
        'country',
        'dist',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
