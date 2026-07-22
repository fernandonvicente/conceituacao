<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('users', UserController::class);

    Route::middleware('admin')->group(function () {
        Route::apiResource('profiles', ProfileController::class);

        Route::get('/users/{user}/profiles', [UserProfileController::class, 'index']);
        Route::put('/users/{user}/profiles', [UserProfileController::class, 'sync']);
    });
});
