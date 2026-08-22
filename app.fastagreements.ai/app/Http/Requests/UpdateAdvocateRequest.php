<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdvocateRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'lawyer_type' => 'required|string|max:255',
            'is_verified' => 'nullable|boolean',
            'price' => 'required|numeric|min:0',
            'consultation_time' => 'required|string|max:255',
            'total_reviews' => 'nullable|integer|min:0',
            'experience' => 'required|string|max:255',
            'about' => 'required|string',
            'languages_known' => 'nullable|array',
            'languages_known.*' => 'string|in:English,Hindi,Gujarati,Marathi,Tamil,Telugu',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4|max:51200',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'expertise' => 'nullable|array',
            'expertise.*' => 'string|in:Criminal Case,Family Case,Civil Case,Property Case,Divorce,Labour Law,Consumer Court,GST,Company Law,Banking,Insurance',
            'degree' => 'nullable|array',
            'degree.*' => 'string|in:B.Com,LL.B,LL.M,B.A. LL.B,BBA LL.B,M.Com,PhD Law',
            'address' => 'required|string',
            'mobile_number' => 'required|string|max:20',
            'status' => 'nullable|boolean',
        ];
    }
}
