<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Feed extends Model
{
    use HasFactory;
    protected $table = 'feeds';

    protected $fillable = ['type', 'customer_id', 'customer_id2', 'agreement_id', 'category_id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function customer2()
    {
        return $this->belongsTo(Customer::class, 'customer_id2', 'id');
    }

    public function category()
    {
        return $this->belongsTo(DealCategory::class, 'category_id', 'id');
    }
    public function agreement()
    {
       return $this->belongsTo(Aggriment::class, 'agreement_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(FeedComment::class,'feed_id','id');
    }

    public function likes()
    {
        return $this->hasMany(FeedLike::class,'feed_id','id');
    }

    public function reports()
    {
        return $this->hasMany(FeedReport::class,'feed_id','id');
    }
}
