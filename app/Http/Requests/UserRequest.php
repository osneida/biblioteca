<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'name'      => 'required|min:3|max:255',
                    'email'     => 'required|string|email|min:8|max:30|unique:users',
                    'password'  => 'required|string|min:8|confirmed', //password_confirmation
                    //'rol'       => 'required|string|max:255',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name'      => 'required|string|min:3|max:255',
                    'password'  => 'required|string|min:8|confirmed', //password_confirmation
                    // 'rol'       => 'required|string|max:255',
                    'email' => [
                        'required',
                        'string',
                        'email',
                        'min:8',
                        'max:30',
                        Rule::unique('users')->ignore($this->route('users')),
                    ]
                ];
            default:
                return [];
        }
    }
}
