<?php

use App\Http\Controllers\Api\Admin\AutorController;
use App\Http\Controllers\Api\Admin\CatalogoController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::post('auth/refresh', [AuthController::class, 'refresh']);
Route::post('auth/me', [AuthController::class, 'me']);

Route::middleware('auth:api')->group(function () {
    //  Route::get('autores', [AutorController::class, 'index']);
    // Route::get('autores/{id}', [AutorController::class, 'show']);
    Route::apiResource('autores', AutorController::class)->names('autores');
    Route::apiResource('catalogos', CatalogoController::class)->names('catalogos');
});
