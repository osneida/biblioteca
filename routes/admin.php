<?php

use App\Http\Controllers\Api\Admin\AutorController;
use App\Http\Controllers\Api\Admin\CatalogoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('autores', AutorController::class)->names('autores');
    Route::apiResource('catalogos', CatalogoController::class)->names('catalogos');
});
