<?php

use App\Http\Controllers\Api\Admin\AutorController;
use App\Http\Controllers\Api\Admin\CatalogoController;
use App\Http\Controllers\Api\Admin\EditorialController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('autor', AutorController::class)->names('autores');
    Route::apiResource('editorial', EditorialController::class)->names('editoriales');
    Route::apiResource('catalogo', CatalogoController::class)->names('catalogos');
});
