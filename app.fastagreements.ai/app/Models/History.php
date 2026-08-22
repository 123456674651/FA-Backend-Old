<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $table = 'histories';

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->images ? asset('admin/images/remark_images_thumb/' . $this->images) : null;
    }

    

    public function cutomers()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id')->select('id', 'name','mobile')->withDefault();
    }
}
