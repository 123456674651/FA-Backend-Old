<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;

        protected $fillable = [
            'user_id',
            'device_token_hash',
            'device_token',
            'counter',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
}
    