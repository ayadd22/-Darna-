<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

// ----- Static collection endpoints  -----

Route::get('/properties/featured', [PropertyController::class, 'featured']);

Route::get('/properties/stats/by-governorate', [PropertyController::class, 'governorateStats']);

// ----- Standard property CRUD + approval -----

Route::get('/properties', [PropertyController::class, 'index']);

Route::post('/properties', [PropertyController::class, 'store'])
    ->middleware('auth:sanctum');

Route::get('/properties/{property}', [PropertyController::class, 'show']);

Route::put('/properties/{property}', [PropertyController::class, 'update'])
    ->middleware('auth:sanctum');

Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])
    ->middleware('auth:sanctum');

Route::patch('/properties/{property}/approve', [PropertyController::class, 'approve'])
    ->middleware('auth:sanctum');

Route::patch('/properties/{property}/reject', [PropertyController::class, 'reject'])
    ->middleware('auth:sanctum');
// ----- Property image management -----

Route::post('/properties/{property}/images', [PropertyController::class, 'storeImage'])
    ->middleware('auth:sanctum');

Route::delete('/properties/{property}/images/{image}', [PropertyController::class, 'destroyImage'])
    ->middleware('auth:sanctum');