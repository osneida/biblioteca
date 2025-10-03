<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CatalogoRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titulo'            => 'required|string|max:255',
            'subtitulo'         => 'nullable|string|max:255',
            'tipo_documento'    => 'required|integer|min:1',
            'fecha_publicacion' => 'nullable|date',
            'descripcion_fisica' => 'nullable|string|max:255',
            'notas'             => 'nullable|string',
            'isbn'              => 'nullable|string|max:13|unique:catalogos,isbn,' . $this->route('catalogo'),
            'fecha_ingreso'     => 'nullable|date|before_or_equal:today',
            'editorial_id'      => 'required|exists:editorials,id',
            'autores'           => 'required|array|min:1',
            'autores.*'         => 'exists:autors,id',
            // 'ejemplares' => 'nullable|array|min:1',
            // 'ejemplares.*.nro_ejemplar' => 'required|string|max:50',
            // 'ejemplares.*.codigo' => 'required|string|max:50|unique:ejemplares,codigo,' . ($this->route('catalogo') ? $this->route('catalogo') : 'NULL') . ',catalogo_id',
            // 'ejemplares.*.estatus' => 'required|in:disponible,no_disponible,reservado,prestado',
        ];
    }
}
