<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['file_path', 'category_id', 'language_id'];
  
   public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
  
   public function category()
    {
        return $this->belongsTo(DealCategory::class, 'category_id');
    }

}
