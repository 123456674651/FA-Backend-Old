<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationHistory extends Model
{
    use HasFactory;

    protected $table = 'notification_histories';

    protected $fillable = [
        'title',
        'message',
        'image',
        'notification_type',
        'total_recipients',
        'success_count',
        'failed_count',
        'sent_by',
        'scheduled_at',
        'status'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function recipients()
    {
        return $this->hasMany(NotificationHistoryUser::class, 'notification_history_id');
    }
}
