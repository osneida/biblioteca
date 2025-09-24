<?php

namespace App\Http\Resources;

use App\Enums\NacionalidadEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'nacionalidad' => $this->nacionalidad,
            'nacionalidad_label' => NacionalidadEnum::tryFrom($this->nacionalidad)?->label() ?? '', // Convertir el valor a etiqueta
        ];
    }

    public function with($request)
    {
        return [
            'message' => $this->additional['message'] ?? null,
        ];
    }
}
