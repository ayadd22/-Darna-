<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::get('/properties', [PropertyController::class, 'index']);

Route::post('/properties', [PropertyController::class, 'store'])
    ->middleware('auth:sanctum');

Route::get('/properties/{property}', [PropertyController::class, 'show']);

Route::put('/properties/{property}', [PropertyController::class, 'update'])
    ->middleware('auth:sanctum');

Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])
    ->middleware('auth:sanctum');
