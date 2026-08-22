<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryWarning extends Model
{
    use HasFactory;

    protected $table = 'deal_category_warnings';

    protected $fillable = [
        'deal_category_id',
        'language_id',
        'title',
        'description',
        'image',
        'display_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the deal category that owns this warning.
     */
    public function dealCategory()
    {
        return $this->belongsTo(DealCategory::class, 'deal_category_id');
    }

    /**
     * Get the language associated with this warning.
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
