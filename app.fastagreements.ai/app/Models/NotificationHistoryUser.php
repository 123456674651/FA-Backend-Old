<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationHistoryUser extends Model
{
    use HasFactory;

    protected $table = 'notification_history_users';

    protected $fillable = [
        'notification_history_id',
        'customer_id',
        'fcm_token',
        'delivery_status',
        'failure_reason',
        'firebase_response'
    ];

    public function notificationHistory()
    {
        return $this->belongsTo(NotificationHistory::class, 'notification_history_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
