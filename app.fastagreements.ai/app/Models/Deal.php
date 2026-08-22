<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_1',
        'person_2',
        'amount',
        'purpose',
        'video_recording',
        'audio_recording',
        'images',
        'category_id',
        'language_id',
        'contract_template_file_name_id',
        'from_date',
        'to_date',
        'contract_date',
        'status',
        'lat',
        'lon',
        'allow_live_location',
        'interest_rate',
        'payable_amount',
        'interest_term_in_month',
        'sakshi',
        'mediator_id',
        'currency',
        'notes',
    ];
}
