<?php

namespace App\Http\Requests;

use App\Enums\NacionalidadEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'              => 'required|string|min:3|max:100|unique:autors,nombre,except,id',
            'nacionalidad'        => [
                'required',
                'string',
                'min:1',
                'max:1',
                Rule::in(NacionalidadEnum::values()),
            ],
            'fecha_nacimiento'    => 'nullable|date',
            'fecha_fallecimiento' => 'nullable|date|after_or_equal:fecha_nacimiento',
            'biografia'           => 'nullable|string',
        ];
    }
}
