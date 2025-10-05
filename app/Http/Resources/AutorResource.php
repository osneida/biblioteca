<?php

namespace App\Http\Resources;

use App\Enums\NacionalidadEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Solo mostrar los atributos realmente cargados (por SelectScope),
        // incluir 'id' solo si está en los atributos seleccionados o si no hay filtro select
        $data = [];
        $attributes = $this->resource->getAttributes();
        $fillable = method_exists($this->resource, 'getFillable') ? $this->resource->getFillable() : array_keys($attributes);

        $select = request('select');
        $selectArray = $select ? explode(',', $select) : null;

        // Incluir 'id' si está en los atributos seleccionados o si no hay filtro select
        if ((is_array($selectArray) && in_array('id', $selectArray)) || is_null($selectArray)) {
            $data['id'] = $this->id;
        }
        foreach ($attributes as $field => $value) {
            if (!in_array($field, $fillable)) continue;
            // Evitar duplicar 'id'
            if ($field === 'id' && isset($data['id'])) continue;
            switch ($field) {
                case 'nacionalidad':
                    $data['nacionalidad'] = $this->nacionalidad;
                    $data['nacionalidad_label'] = NacionalidadEnum::tryFrom($this->nacionalidad)?->label() ?? ''; // Convertir el valor a etiqueta
                    break;
                default:
                    $data[$field] = $value;
            }
        }

        // Incluir relaciones que puedan haber sido solicitadas por include=
        if ($this->relationLoaded('catalogos')) {
            $data['catalogos'] = CatalogoResource::collection($this->catalogos);
        }

        return $data;
    }
}
