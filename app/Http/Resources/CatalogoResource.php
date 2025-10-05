<?php

namespace App\Http\Resources;

use App\Enums\TipoDocumentoEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Solo mostrar los atributos realmente cargados (por SelectScope),
        // incluir 'id' solo si está en los atributos seleccionados o si no hay filtro select
        $data = [];
        $attributes = $this->resource->getAttributes();
        $fillable = method_exists($this->resource, 'getFillable') ? $this->resource->getFillable() : array_keys($attributes);

        $select = $request->query('select');
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
                case 'tipo_documento':
                    $data['tipo_documento'] = $this->tipo_documento;
                    $data['tipo_documento_label'] = TipoDocumentoEnum::tryFrom($this->tipo_documento)?->label() ?? '';
                    break;
                // case 'editorial_id':
                //     $data['editorial_id'] = $this->editorial_id;
                //     break;
                default:
                    $data[$field] = $value;
            }
        }

        // Incluir relaciones que puedan haber sido solicitadas por include=
        if ($this->relationLoaded('autores')) {
            $data['autores'] = AutorResource::collection($this->autores);
        }

        if ($this->relationLoaded('editorial')) {
            $data['editorial'] = new EditorialResource($this->editorial);
        }

        if ($this->relationLoaded('ejemplares')) {
            $data['ejemplares'] = EjemplarResource::collection($this->ejemplares);
        }

        return $data;
    }
}
