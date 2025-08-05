<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use App\Models\User;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules($userID = null): array
    {
        if (!$userID) {
            return [
                'name'     => ['required', 'string', 'max:100'],
                'email'    => ['required', 'string', 'lowercase', 'email', 'max:100', 'unique:' . User::class],
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            ];
        } else {
            return [
                'name'     => ['required', 'string', 'max:100'],
                'email'    => ['required', 'string', 'lowercase', 'email', 'max:100', 'unique:users,email,'.$userID],
            ];
        }
    }
}
