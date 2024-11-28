<?php

use App\Http\Controllers\HistoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth No Middleware
Route::prefix('auth')->group(function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    // User
    Route::prefix('user')->group(function () {
        // Get All Account User
        Route::get('/all', [UserController::class, 'index']);
        
        // Detail Account User
        Route::get('/detail-account/{id}', [UserController::class, 'show']);

        // Update Account User
        Route::put('/update-account/{id}', [UserController::class, 'update']);

        // Delete Account User
        Route::delete('/delete-account/{id}', [UserController::class, 'destroy']);

        // Change Password
        Route::put('/change-password/{id}', [UserController::class, 'changePassword']);

        // Logout
        Route::post('/logout', [UserController::class, 'logout']);
    });

    // History (Controller Pending)
    Route::prefix('history')->group(function () {
        // Get All History
        Route::get('/all-history', [HistoryController::class, 'index']);

        // Get History By User ID
        Route::get('/history-by-user/{idUser}', [HistoryController::class, 'showHistoryUserById']);

        // Get History By ID
        Route::get('/detail-history/{id}', [HistoryController::class, 'show']);

        // Create History
        Route::post('/create-history', [HistoryController::class, 'store']);

        // Update History
        // Route::put('/update-history/{id}', [HistoryController::class, 'update']);

        // Delete History
        Route::delete('/delete-history/{id}', [HistoryController::class, 'destroy']);
    });
});