<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalNoticeReply extends Model
{
    use HasFactory;

    protected $table = 'legal_notice_replies';

    protected $fillable = [
        'legal_notice_id',
        'admin_id',
        'message',
        'status',
    ];

    /**
     * Get the legal notice associated with this reply.
     */
    public function legalNotice()
    {
        return $this->belongsTo(LegalNotice::class, 'legal_notice_id');
    }

    /**
     * Get the admin that sent the reply.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
