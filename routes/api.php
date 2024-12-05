<?php

use App\Http\Controllers\HistoryController;
use App\Http\Controllers\SubscriptionController;
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

    // History 
    Route::prefix('history')->group(function () {
        // Get All History
        Route::get('/all', [HistoryController::class, 'index']);

        // Get History By User ID
        Route::get('/history-by-user/{idUser}', [HistoryController::class, 'showHistoryUserById']);

        // Get History By ID
        Route::get('/detail/{id}', [HistoryController::class, 'show']);

        // Create History
        Route::post('/create', [HistoryController::class, 'store']);
        
        // Delete History
        Route::delete('/delete/{id}', [HistoryController::class, 'destroy']);

        // Count History
        Route::get('/count/{idUser}', [HistoryController::class, 'countHistory']);
        
        // Update History
        // Route::put('/update-history/{id}', [HistoryController::class, 'update']);
    });

    // Subscription
    Route::prefix('subscription')->group(function () {
        // Get All Subscription
        Route::get('/all', [SubscriptionController::class, 'index']);

        // Show Detail User Subscription id -> user
        Route::get('/detail/{id}', [SubscriptionController::class, 'show']);

        // Create Subscription
        Route::post('/create', [SubscriptionController::class, 'store']);

        // Update Status Subscription id -> subs
        Route::put('/update-status/{id}', [SubscriptionController::class, 'updateStatus']);

        // Delete Subscription
        Route::delete('/delete/{id}', [SubscriptionController::class, 'destroy']);

        // Get Subscription By User ID
        Route::get('/user/{idUser}', [SubscriptionController::class, 'listSubsUser']);
    });

});