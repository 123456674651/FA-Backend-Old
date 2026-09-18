<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aggriment extends Model
{
   use HasFactory;

   protected $table = 'agreements';

   protected $appends = [
      'party_one_image_url', 
      'party_two_image_url',
      'documents_url',
      'vehicle_front_side_url',
      'vehicle_back_side_url',
      'vehicle_left_side_url',
      'vehicle_right_side_url'
   ];


   public function party1()
   {
      return $this->belongsTo(Customer::class, 'party_1_id', 'id');
   }

   public function party2()
   {
      return $this->belongsTo(Customer::class, 'party_2_id', 'id');
   }

   public function user1()
   {
      return $this->belongsTo(User::class, 'party_1_id', 'id');
   }

   public function user2()
   {
      return $this->belongsTo(User::class, 'party_2_id', 'id');
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

   public function installments()
   {
      return $this->hasMany(Installment::class, 'agreement_id', 'id');
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

   public function getDocumentsUrlAttribute()
   {
      return $this->documents ? asset('agreement_pdfs/' . $this->documents) : null;
   }

   public function getVehicleFrontSideUrlAttribute()
   {
      return $this->vehicle_front_side ? asset('admin/images/vehicle_images/' . $this->vehicle_front_side) : null;
   }

   public function getVehicleBackSideUrlAttribute()
   {
      return $this->vehicle_back_side ? asset('admin/images/vehicle_images/' . $this->vehicle_back_side) : null;
   }

   public function getVehicleLeftSideUrlAttribute()
   {
      return $this->vehicle_left_side ? asset('admin/images/vehicle_images/' . $this->vehicle_left_side) : null;
   }

   public function getVehicleRightSideUrlAttribute()
   {
      return $this->vehicle_right_side ? asset('admin/images/vehicle_images/' . $this->vehicle_right_side) : null;
   }
  
   public function invoice()
{
    return $this->belongsTo(SubscriptionInvoice::class, 'invoice_id', 'id');
}
}
