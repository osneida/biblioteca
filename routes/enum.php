<?php

use App\Http\Controllers\Api\Admin\EnumController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('enums/tipos-documento', [EnumController::class, 'tiposDocumento']);
    Route::get('enums/estatus-disponibilidad', [EnumController::class, 'estatusDisponibilidad']);
    Route::get('enums/nacionalidades', [EnumController::class, 'nacionalidades']);
});
