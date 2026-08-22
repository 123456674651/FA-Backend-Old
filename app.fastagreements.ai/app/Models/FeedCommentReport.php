<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FeedCommentReport extends Model
{
    use HasFactory;
    protected $table = 'feed_comments_reports';

    protected $fillable = ['feed_comment_id', 'customer_id','reason','status'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function feedComment()
    {
        return $this->belongsTo(FeedComment::class, 'feed_comment_id', 'id');
    }
}
