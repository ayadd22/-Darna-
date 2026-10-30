<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\GovernorateController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OwnerController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\OptionalSanctumAuth;


Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

// Public property endpoints

Route::get('/properties/featured', [PropertyController::class, 'featured']);

Route::get('/properties/stats/by-governorate', [PropertyController::class, 'governorateStats']);

Route::get('/properties', [PropertyController::class, 'index'])
    ->middleware(OptionalSanctumAuth::class);

Route::get('/properties/{property}', [PropertyController::class, 'show'])
    ->middleware(OptionalSanctumAuth::class);

// Property management: Admin and Office

Route::middleware(['auth:sanctum', 'role:admin,office'])->group(function () {
    Route::post('/properties', [PropertyController::class, 'store']);

    Route::put('/properties/{property}', [PropertyController::class, 'update']);

    Route::delete('/properties/{property}', [PropertyController::class, 'destroy']);

    Route::post('/properties/{property}/images', [PropertyController::class, 'storeImage']);

    Route::delete('/properties/{property}/images/{image}', [PropertyController::class, 'destroyImage']);
});

// Property approval: Admin only

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::patch('/properties/{property}/approve', [PropertyController::class, 'approve']);

    Route::patch('/properties/{property}/reject', [PropertyController::class, 'reject']);
});

// Base data

Route::get('/governorates', [GovernorateController::class, 'index']);

Route::get('/offices', [OfficeController::class, 'index']);

Route::get('/owners', [OwnerController::class, 'index'])
    ->middleware(['auth:sanctum', 'role:admin,office']);
