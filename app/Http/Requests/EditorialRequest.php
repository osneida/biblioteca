<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditorialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'nombre'    => 'required|string|min:3|max:100|unique:editorials,nombre,except,id',
            'direccion' => 'nullable|string|min:3|max:255',
        ];
    }
}
