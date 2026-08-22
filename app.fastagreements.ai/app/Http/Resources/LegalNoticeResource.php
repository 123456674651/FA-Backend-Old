<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LegalNoticeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'total_amount' => $this->total_amount,
            'amount_due' => $this->amount_due,
            'company_person_name' => $this->company_person_name,
            'company_person_designation' => $this->company_person_designation,
            'company_address' => $this->company_address,
            'my_company_name' => $this->my_company_name,
            'my_company_business_nature' => $this->my_company_business_nature,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
