<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalNoticeNotification extends Model
{
    use HasFactory;

    protected $table = 'legal_notice_notifications';

    protected $fillable = [
        'user_id',
        'legal_notice_id',
        'title',
        'message',
        'is_read',
    ];

    /**
     * Get the user that receives the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the legal notice associated with the notification.
     */
    public function legalNotice()
    {
        return $this->belongsTo(LegalNotice::class, 'legal_notice_id');
    }
}
