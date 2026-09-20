<?php
// routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\PresentationController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\ProfileController;

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

Route::middleware('auth:sanctum')->group(function () {
    // ... существующие маршруты

    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);

    Route::get('/events/{event}/presentations', [PresentationController::class, 'byEvent']);
    Route::get('/presentations/{presentation}', [PresentationController::class, 'show']);

    Route::post('/assessments', [AssessmentController::class, 'store']);

    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::post('/change-password', [ProfileController::class, 'changePassword']);
});

