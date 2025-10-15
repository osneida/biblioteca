<?php

namespace App\Http\Requests;

use App\Enums\EstatusDisponibilidadEnum;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class EjemplarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'catalogo_id'   => 'required|exists:catalogos,id',
            'nro_ejemplar'  => 'required|integer|min:1',
            'codigo'        => 'required|string|max:50|unique:ejemplars,codigo,except,id',
            'estatus'       => 'required|string|min:1|max:1|' . Rule::in(EstatusDisponibilidadEnum::values()),
            'fecha_ingreso' => 'required|date|before_or_equal:today',
        ];
    }
}
