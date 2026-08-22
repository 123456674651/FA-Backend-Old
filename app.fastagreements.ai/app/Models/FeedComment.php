<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FeedComment extends Model
{
    use HasFactory;
    protected $table = 'feed_comments';

    protected $fillable = ['feed_id', 'customer_id', 'comment'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function feed()
    {
        return $this->belongsTo(Feed::class, 'feed_id', 'id');
    }

    public function likes()
    {
        return $this->hasMany(FeedCommentLike::class, 'feed_comment_id', 'id');
    }

    public function reports()
    {
        return $this->hasMany(FeedCommentReport::class, 'feed_comment_id', 'id');
    }
}
