<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EjemplarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'catalogo_id' => $this->catalogo_id,
            'nro_ejemplar' => $this->nro_ejemplar,
            'codigo' => $this->codigo,
            'estatus' => $this->estatus
        ];
    }
}
