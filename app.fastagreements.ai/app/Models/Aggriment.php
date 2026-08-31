<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aggriment extends Model
{
   use HasFactory;

   protected $table = 'agreements';

   protected $appends = ['party_one_image_url', 'party_two_image_url'];


   public function party1()
   {
      return $this->belongsTo(Customer::class, 'party_1_id', 'id');
   }

   public function party2()
   {
      return $this->belongsTo(Customer::class, 'party_2_id', 'id');
   }

   public function category()
   {
      return $this->belongsTo(DealCategory::class, 'category_id', 'id');
   }

   public function subCategory()
   {
      return $this->belongsTo(DealCategory::class, 'sub_category', 'id');
   }

   public function language()
   {
      return $this->belongsTo(Language::class, 'aggriment_language_id', 'id');
   }

   public function attributes()
   {
      return $this->hasMany(AgreementAttribute::class, 'agreement_id', 'id');
   }

   public function histories()
   {
      return $this->hasMany(History::class, 'agreement_id', 'id');
   }

   public function getPartyOneImageUrlAttribute()
   {
      return asset('admin/images/person_images_thumb/' . $this->party_1_image);

   }

   public function getPartyTwoImageUrlAttribute()
   {
      return asset('admin/images/person_images_thumb/' . $this->party_2_image);

   }
  
   public function invoice()
{
    return $this->belongsTo(SubscriptionInvoice::class, 'invoice_id', 'id');
}
}
