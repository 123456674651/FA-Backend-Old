<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'role' => ['required', Rule::in(['SUPER_ADMIN', 'ADMIN'])],
            'password' => 'required|string|min:6|confirmed',
            'status' => 'nullable|boolean',
        ];
    }
}
