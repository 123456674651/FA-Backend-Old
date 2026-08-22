<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLegalNoticeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'amount_due' => 'required|numeric|min:0',
            'company_person_name' => 'required|string|max:255',
            'company_person_designation' => 'required|string|max:255',
            'company_address' => 'required|string',
            'my_company_name' => 'required|string|max:255',
            'my_company_business_nature' => 'required|string|max:255',
        ];
    }
}
