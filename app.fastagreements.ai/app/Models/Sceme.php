<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sceme extends Model
{
    use HasFactory;

    protected $fillable = [
        'emi_pay_method',
        'is_active'
    ];

    public $appends = [
        'months'
    ];

    public function getMonthsAttribute()
    {
        $number = (int) preg_replace('/\D/', '', $this->emi_pay_method);

        return $number;

    }
}
