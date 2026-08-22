<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advocate extends Model
{
    use HasFactory;

    protected $table = 'advocates';

    protected $fillable = [
        'image',
        'name',
        'lawyer_type',
        'is_verified',
        'price',
        'consultation_time',
        'total_reviews',
        'experience',
        'about',
        'languages_known',
        'video',
        'document',
        'expertise',
        'degree',
        'address',
        'mobile_number',
        'status',
    ];

    protected $casts = [
        'languages_known' => 'array',
        'expertise' => 'array',
        'degree' => 'array',
        'is_verified' => 'boolean',
        'status' => 'boolean',
    ];
}
