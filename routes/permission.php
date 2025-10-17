<?php

use App\Http\Controllers\Api\Permisos\PermissionControles;
use App\Http\Controllers\Api\Permisos\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('permissions', PermissionControles::class);
    Route::apiResource('roles', RoleController::class);
});