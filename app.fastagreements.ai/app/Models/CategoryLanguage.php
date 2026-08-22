<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryLanguage extends Model
{
    use HasFactory;

    protected $table = 'category_languages';

    protected $appends = [
        'category_name',
        'language_name'
    ];


    protected $fillable = [
        'category_id',
        'language_id',
        'agreement_text'
    ];

    // Define the relationship
    public function dealCategory()
    {
        return $this->belongsTo(DealCategory::class, 'category_id', 'id');
    }

    // Accessor for category name
    public function getCategoryNameAttribute()
    {
        return $this->dealCategory ? $this->dealCategory->category_name : null;
    }


    //    Define the relationship with language table 
    public function languages()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    // Accessor for language name 

    public function getLanguageNameAttribute()
    {
        return $this->languages ? $this->languages->language_name : null;
    }
}
