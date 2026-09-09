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
        $adminId = $this->route('user') ?? $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('admins', 'email')->ignore($adminId),
            ],
            'role' => ['required', Rule::in(['SUPER_ADMIN', 'ADMIN'])],
            'password' => 'nullable|string|min:6|confirmed',
            'status' => 'nullable|boolean',
        ];
    }
}
