<?php

namespace App\Http\Resources;

use App\Enums\TipoDocumentoEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogoResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha_registro' => $this->fecha_registro,
            'tipo_documento' => $this->tipo_documento,
            'tipo_documento_label' => TipoDocumentoEnum::tryFrom($this->tipo_documento)?->label() ?? '', // Convertir el valor numérico a etiqueta
            'isbn' => $this->isbn,
            'titulo' => $this->titulo,
            'sub_titulo' => $this->sub_titulo,
            'autor_id' => $this->autor_id,
            'autor' =>  new AutorResource($this->whenLoaded('autor')),
            'editorial_id' => $this->editorial_id,
            'editorial' => new EditorialResource($this->whenLoaded('editorial')),
        ];
    }

    public function with($request)
    {
        return [
            'message' => $this->additional['message'] ?? null,
        ];
    }
}
