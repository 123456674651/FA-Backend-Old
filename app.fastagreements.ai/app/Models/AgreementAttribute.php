<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgreementAttribute extends Model
{
    use HasFactory;

    protected $table = 'agreement_attribute';
    protected $append = 'value';
   
    protected $fillable = [
    'agreement_id',
    'attribute_id',
    'attribute_value',
      'scores'
];


    public function agreement()
    {
        $this->belongsTo(Aggriment::class, 'id', 'agreement_id');
    }

    public function categoryAttribute()
    {
        return $this->belongsTo(CategoryAttribute::class, 'attribute_id', 'id')->withDefault();
    }

    public function getValueAttribute()
    {
        switch ($this->attribute_value) {
            case 0:
                return "No";
                break;
            case 1:
                return "Yes";
                break;
            
            default:
            return $this->attribute_value;
                break;
        }
    }
}
