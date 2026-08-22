<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalNotice extends Model
{
    use HasFactory;

    protected $table = 'legal_notices';

    protected $fillable = [
        'company_name',
        'total_amount',
        'amount_due',
        'company_person_name',
        'company_person_designation',
        'company_address',
        'my_company_name',
        'my_company_business_nature',
        'user_id',
        'status',
    ];

    /**
     * Get the user that owns the legal notice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer that owns the legal notice.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    /**
     * Get the notifications for the legal notice.
     */
    public function notifications()
    {
        return $this->hasMany(LegalNoticeNotification::class, 'legal_notice_id');
    }

    /**
     * Get the replies for the legal notice.
     */
    public function replies()
    {
        return $this->hasMany(LegalNoticeReply::class, 'legal_notice_id');
    }
}
