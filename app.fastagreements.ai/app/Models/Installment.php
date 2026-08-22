<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Installment extends Model
{
    use HasFactory;
    protected $appends = ['status_value'];


    protected $table = 'installments';

    protected $fillable = [
        'agreement_id',
        'emi_amount',
        'emi_date',
        'status'
    ];

    public function getStatusValueAttribute()
    {
        $emi_date = Carbon::parse($this->emi_date);
        $current_date = Carbon::now();

        $diffrent_date = $current_date->diffInDays($emi_date, false);

        $interval = $current_date->diff($emi_date);
        $difference_formatted = $interval->format('The next installment is in %a days.');

        if ($this->status == 1) {
            return "Paid";
        } elseif ($current_date->gt($emi_date) && $this->status == 0) {
            return "Overdue";
        } elseif ($this->status == 0 &&  $diffrent_date <= 15) {
            return $difference_formatted;
        } else {
            return null;
        }
    }

    public function agreement()
    {
        return $this->belongsTo(Installment::class, 'agreement_id', 'id');
    }


}
