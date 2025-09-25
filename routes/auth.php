<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::post('auth/refresh', [AuthController::class, 'refresh']);
Route::post('auth/me', [AuthController::class, 'me']);
