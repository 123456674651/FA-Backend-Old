<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\hasMany;

use Illuminate\Database\Eloquent\Model;
use App\Models\CategoryAttribute;

class DealCategory extends Model
{
    use HasFactory;

    public $appends = ['category_image_url','lable_list','visuality_list'];

    protected $fillable = [
        'category_name',
        'category_image',
        'is_active',
        'deal_price',
        // Per-tier pricing. Both optional — deal_price stays the fallback for
        // any category that has never been given a split price.
        'price_with_otp',
        'price_without_otp',
        'is_on_interest',
        'description',
        'parent_id'
    ];

    public function getCategoryImageUrlAttribute()
    {
        return asset('admin/images/category_image_thumb/' . $this->category_image);
    }
    
    public function children() 
    {
        return $this->hasMany(self::class, 'parent_id')->with('attribute_list');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
    
      public function attribute_list() : HasMany
    {
        // return [1, 2, 4];
        return $this->hasMany(CategoryAttribute::class, 'category_id',  'id')->where('is_active', 1)->orderBy('sort_order','ASC');
    }
  
  
    public function getLableListAttribute()
{
    // Ensure $this->lable is a string before processing
    if (is_string($this->lable) && !empty($this->lable)) {
        $data = rtrim($this->lable, ','); // Remove trailing comma
        $labelArray = explode(',', $data); // Split the string by commas
        $parsedLabels = [];

        foreach ($labelArray as $label) {
            // Split each key-value pair by the delimiter `=>`
            $parts = explode('=>', $label);
            if (count($parts) === 2) {
                // Trim spaces and quotes, then assign to the associative array
                $key = trim($parts[0]);
                $value = trim($parts[1], ' "'); // Remove surrounding quotes if any
                $parsedLabels[$key] = $value;
            }
        }

        return $parsedLabels; // Return parsed associative array
    }

    // If $this->lable is not a valid string or is empty, return an empty array
    return [];
}
  public function getVisualityListAttribute()
{
    // Handle if already object
    if (is_object($this->visuality)) {
        return (array) $this->visuality;
    }

    //  Ensure it's a valid string
    if (is_string($this->visuality) && !empty($this->visuality)) {

        $data = rtrim($this->visuality, ',');
        $items = explode(',', $data);

        $parsed = [];

        foreach ($items as $item) {

            $item = trim($item);

            if (!$item || !str_contains($item, '=>')) {
                continue;
            }

            $parts = explode('=>', $item, 2);

            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = strtolower(trim($parts[1]));

                // Convert to boolean
                if ($value === 'true') {
                    $value = true;
                } elseif ($value === 'false') {
                    $value = false;
                }

                $parsed[$key] = $value;
            }
        }

        return $parsed; // 👈 return ARRAY (same as lable_list)
    }

    return [];
}
  

    
    //  public function getLableListAttribute(){
    //     if (!empty($this->lable)) {
    //         $data = rtrim($this->lable, ','); // Remove trailing comma
    //         $labelArray = explode(',', $data);    // Split the string by commas
    //         $parsedLabels = [];

    //         foreach ($labelArray as $label) {
    //             // Split each key-value pair by the delimiter `=>`
    //             $parts = explode('=>', $label);
    //             if (count($parts) === 2) {
    //                 // Trim spaces and quotes, then assign to the associative array
    //                 $key = trim($parts[0]);
    //                 $value = trim($parts[1], ' "'); // Remove surrounding quotes if any
    //                 $parsedLabels[$key] = $value;
    //             }
    //         }

    //       return  $this->lable = $parsedLabels;
    //     }
    //  }

    /**
     * Get the category warnings for the deal category.
     */
    public function categoryWarnings()
    {
        return $this->hasMany(CategoryWarning::class, 'deal_category_id');
    }
}
