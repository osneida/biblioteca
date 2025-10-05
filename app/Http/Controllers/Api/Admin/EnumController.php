<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EnumController extends Controller
{
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
