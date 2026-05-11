<?php

namespace App\Http\Requests;

use App\Enums\DepartmentEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class RegisterRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s]+$/'
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email'
            ],

            'password' => [
                'required',
                'min:6'
            ],

            'role' => [
                'required',
                'string',
                'exists:roles,name'
            ],

            'department' => [
                'nullable',
                'string',
                'exists:departments,name'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Name can only contain letters and spaces',
            'email.unique' => 'Email already exists',
        ];
    }
}
