<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsPage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cms_pages';

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'description',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'featured_image',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the user who created the CMS page.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the CMS page.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
