<?php
// routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Публичные маршруты
Route::post('/login', [AuthController::class, 'login']);

// Защищенные маршруты
Route::middleware('auth:sanctum')->group(function () {
    // Аутентификация
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/check', [AuthController::class, 'check']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Администраторские маршруты
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/reset-password/{user}', [AuthController::class, 'resetPassword']);
    });
});

// Тестовые маршруты
Route::get('/test', function () {
    return response()->json(['message' => 'API работает!']);
});

// Тестовые маршруты для проверки ролей
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin-only', [TestController::class, 'adminOnly']);
});

Route::middleware(['auth:sanctum', 'role:expert'])->group(function () {
    Route::get('/expert-only', [TestController::class, 'expertOnly']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/all-roles', [TestController::class, 'allRoles']);
});