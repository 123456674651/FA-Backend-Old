<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') ?? $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'mobile' => [
                'required',
                Rule::unique('users', 'mobile')->ignore($userId),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'status' => 'nullable|boolean',
        ];
    }
}
