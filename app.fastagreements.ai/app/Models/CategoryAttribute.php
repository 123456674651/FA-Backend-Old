<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CategoryAttribute extends Model
{
    use HasFactory;

    protected $table = 'category_attributes';

  protected $fillable = [
        'category_id',
        'attribute_name',
        'attribute_code',
        'attribute_values',
        'input_type',
        'default_value',
        'sort_order',
        'is_required',
        'is_active'
    ];
    protected $appends = [
        'attribute_values_data',
        'input_type_name',
        'is_required_name'
    ];


    public function dealCategory()
    {
        return $this->belongsTo(DealCategory::class, 'category_id', 'id');
    }

    public function  getAttributeValuesDataAttribute()
    {

        if (!is_array($this->attribute_values) && $this->input_type == 2) {

            return explode(',', $this->attribute_values);
        } else {
            return $this->attribute_values;
        }
    }

    public function getInputTypeNameAttribute()
    {
        switch ($this->input_type) {
            case '1':
                return "Text";
                break;
            case '2':
                return "Dropdown";
                break;
            case '3':
                return "Switch";
                break;
            case '4':
                return "Date";
                break;
                  case '5':
                return "Heading";
                break;
            default:
            return "Na";
                break;
        }
    }

    public function getIsRequiredNameAttribute()
    {
        switch ($this->is_required) {
            case '1':
                return "Required";
                break;
            case '0':
                return "Not required";
                break;
            default:
            return "Na";
                break;
        }
    }
}
