<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        // Obtener el id del catálogo desde la ruta (puede ser el id o el modelo)
        $catalogoRoute = $this->route('catalogo');
        $catalogoId = null;
        if ($catalogoRoute) {
            if (is_numeric($catalogoRoute)) {
                $catalogoId = $catalogoRoute;
            } elseif (is_object($catalogoRoute) && method_exists($catalogoRoute, 'getKey')) {
                $catalogoId = $catalogoRoute->getKey();
            }
        }

        // Preparar la regla de ISBN: solo aplicar la comprobación de unicidad
        // cuando se envía un ISBN. La unicidad se hace en combinación con
        // la `editorial_id` (scoped unique). Además ignoramos el id del
        // catálogo cuando estamos en update.
        $isbnRule = ['nullable', 'string', 'max:13'];
        if ($this->filled('isbn')) {
            // Solo aplicar regla de unicidad cuando sea una petición de
            // actualización (PUT/PATCH). Para POST dejamos que el
            // controlador haga updateOrCreate y reutilice el registro
            // si corresponde.
            if (in_array($this->method(), ['PUT', 'PATCH'])) {
                $uniqueIsbnRule = Rule::unique('catalogos', 'isbn')
                    ->where(function ($query) {
                        return $query->where('editorial_id', $this->input('editorial_id'));
                    });
                if ($catalogoId) {
                    $uniqueIsbnRule = $uniqueIsbnRule->ignore($catalogoId);
                }
                $isbnRule[] = $uniqueIsbnRule;
            }
        }

        return [
            'titulo'            => 'required|string|max:255',
            'subtitulo'         => 'nullable|string|max:255',
            'tipo_documento'    => 'required|integer|min:1',
            'fecha_publicacion' => 'nullable|date',
            'descripcion_fisica' => 'nullable|string|max:255',
            'notas'             => 'nullable|string',
            'isbn'              => $isbnRule,
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