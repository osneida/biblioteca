<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EnumController extends Controller
{
    /**
     * Tipo de Documentos
     *
     * Tipos de documentos que puede tener un catálogo
     * @param  \App\Enums\TipoDocumentoEnum::cases()
     * @return TipoDocumentoEnum
     */
    public function tiposDocumento()
    {
        $tipos = [];
        foreach (\App\Enums\TipoDocumentoEnum::cases() as $case) {
            $tipos[] = [
                'value' => $case->value,
                'label' => $case->label(),
            ];
        }
        return response()->json(data: ['data' => $tipos]);
    }

    /**
     * Estatus de la Disponibilidad
     *
     * Disponiblilidad de los documentos
     * @param  \App\Enums\EstatusDisponibilidadEnum::cases()
     * @return EstatusDisponibilidadEnum
     */
    public function estatusDisponibilidad()
    {
        $estatus = [];
        foreach (\App\Enums\EstatusDisponibilidadEnum::cases() as $case) {
            $estatus[] = [
                'value' => $case->value,
                'label' => $case->label(),
            ];
        }
        return response()->json(data: ['data' => $estatus]);
    }

    /**
     * Nacionalidades
     *
     * Nacionalidades de los Autores
     * @param  \App\Enums\NacionalidadEnum::cases()
     * @return NacionalidadEnum
     */
    public function nacionalidades()
    {
        $nacionalidades = [];
        foreach (\App\Enums\NacionalidadEnum::cases() as $case) {
            $nacionalidades[] = [
                'value' => $case->value,
                'label' => $case->label(),
            ];
        }
        return response()->json(data: ['data' => $nacionalidades]);
    }
}
