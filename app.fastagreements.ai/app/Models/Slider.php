<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    public $appends = [
        'slider_image_url'
    ];

    // Fields that are mass assignable
    protected $fillable = [
        'title',
        'description',
        'expire_date',
        'image',
        'status',
        'slider_type'
    ];

    /**
     * Accessor for the slider image URL.
     * Generates a URL to access the slider image stored in the public directory.
     */
    public function getSliderImageUrlAttribute()
    {
        return asset('admin/images/sliders/' . $this->image);
    }
}
