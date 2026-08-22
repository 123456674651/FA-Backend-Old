<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purpose extends Model
{
    use HasFactory;

    public $appends = [
        'purpose_image_url'
    ];

    protected $fillable = [
        'purpose_name',
        'purpose_image',
        'is_active'
    ];

    public function getPurposeImageUrlAttribute()
    {
        return asset('admin/images/purpose_image_thumb/' . $this->purpose_image);
    }
}
