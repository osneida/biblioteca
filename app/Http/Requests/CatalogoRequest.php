<?php

namespace App\Http\Requests;

use App\Enums\TipoDocumentoEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // // Obtener el id del catálogo desde la ruta (puede ser el id o el modelo)
        // $catalogoRoute = $this->route('catalogo');
        // $catalogoId = null;
        // if ($catalogoRoute) {
        //     if (is_numeric($catalogoRoute)) {
        //         $catalogoId = $catalogoRoute;
        //     } elseif (is_object($catalogoRoute) && method_exists($catalogoRoute, 'getKey')) {
        //         $catalogoId = $catalogoRoute->getKey();
        //     }
        // }

        // // Preparar la regla de ISBN: solo aplicar la comprobación de unicidad
        // // cuando se envía un ISBN. La unicidad se hace en combinación con
        // // la `editorial_id` (scoped unique). Además ignoramos el id del
        // // catálogo cuando estamos en update.
        // $isbnRule = ['nullable', 'string', 'max:13'];
        // if ($this->filled('isbn')) {
        //     // Solo aplicar regla de unicidad cuando sea una petición de
        //     // actualización (PUT/PATCH). Para POST dejamos que el
        //     // controlador haga updateOrCreate y reutilice el registro
        //     // si corresponde.
        //     if (in_array($this->method(), ['PUT', 'PATCH'])) {
        //         $uniqueIsbnRule = Rule::unique('catalogos', 'isbn')
        //             ->where(function ($query) {
        //                 return $query->where('editorial_id', $this->input('editorial_id'));
        //             });
        //         if ($catalogoId) {
        //             $uniqueIsbnRule = $uniqueIsbnRule->ignore($catalogoId);
        //         }
        //         $isbnRule[] = $uniqueIsbnRule;
        //     }
        // }

        return [
            'titulo'            => 'required|string|max:255|min:3',
            'subtitulo'         => 'nullable|string|max:255|min:3',
            //tipo_documento: Libro = 1, Revista = 2, Novela = 3, Tesis = 4, Pretesis = 5, Periódico = 6, Película  = 7, Música = 8;
            'tipo_documento'    => 'required|integer|min:1|' . Rule::in(TipoDocumentoEnum::values()),
            'ano_publicacion'   => 'required|string|size:4',
            'descripcion_fisica' => 'nullable|string|min:3',
            'notas'             => 'nullable|string|min:3',
            'isbn'              => 'nullable|string|max:13|unique:catalogos,isbn,' . $this->catalogo?->id,
            'editorial_id'      => 'required|exists:editorials,id',
            'autores'           => 'required|array|min:1',
            'autores.*'         => 'exists:autors,id',
            //fecha_ingreso: para usar en ejmplares.
            'fecha_ingreso'     => 'required|date|before_or_equal:today',
            //cantidad_de_ejemplares: crea la cantidad de ejemplares indicada, sino se indica crea un ejemplar
            'cantidad_de_ejemplares' => 'nullable|integer|min:1',
        ];
    }
}
