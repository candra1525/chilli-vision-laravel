<?php

use App\Http\Controllers\HistoriesController;
use App\Http\Controllers\HistorySubscriptionsController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

// Auth No Middleware
Route::prefix('auth')->group(function () {
    Route::post('/login', [UsersController::class, 'login']); // OK
    Route::post('/register', [UsersController::class, 'register']); // OK
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    // User
    Route::prefix('user')->group(function () {
        // Get All Account User
        Route::get('/all', [UsersController::class, 'index']); // OK

        // Detail Account User
        Route::get('/detail-account/{id}', [UsersController::class, 'show']); // OK

        // Update Account User
        Route::post('/update-account/{id}', [UsersController::class, 'update']); // OK

        // Delete Account User
        Route::delete('/delete-account/{id}', [UsersController::class, 'destroy']); // OK

        // Change Password
        Route::put('/change-password/{id}', [UsersController::class, 'changePassword']); // OK

        // Count User
        Route::get('/count', [UsersController::class, 'countUser']); // OK

        // Logout
        Route::post('/logout', [UsersController::class, 'logout']); // OK
    });

    // History 
    Route::prefix('history')->group(function () {
        // Get All History
        Route::get('/all', [HistoriesController::class, 'index']); // OK

        // Get History By User ID
        Route::get('/history-by-user/{idUser}', [HistoriesController::class, 'indexByIdUser']); // OK

        // Get History By ID
        Route::get('/detail/{id}', [HistoriesController::class, 'show']); // OK

        // Create History
        Route::post('/create', [HistoriesController::class, 'store']); // OK

        // Delete History
        Route::delete('/delete/{id}', [HistoriesController::class, 'destroy']);

        // Count History
        Route::get('/count/{idUser}', [HistoriesController::class, 'countHistory']); // OK

        // Update History
        // Route::put('/update-history/{id}', [HistoryController::class, 'update']);
    });

    // Subscription
    Route::prefix('subscriptions')->group(function () {
        // Get All Subscription
        Route::get('/all', [SubscriptionsController::class, 'index']); // OK

        // Show Detail User Subscription id -> user
        Route::get('/detail/{id}', [SubscriptionsController::class, 'show']); // OK

        // Create Subscription
        Route::post('/create', [SubscriptionsController::class, 'store']); // OK

        // Update Subscription
        Route::post('/update/{id}', [SubscriptionsController::class, 'update']); // OK

        // Delete Subscription
        Route::delete('/delete/{id}', [SubscriptionsController::class, 'destroy']); // OK

        // Count Subscription
        Route::get('/count', [SubscriptionsController::class, 'countSubscriptions']); // OK
    });

    // History Subscription
    Route::prefix('history-subscriptions')->group(function () {
        // Get List By Id User
        Route::get('/list/{idUser}', [HistorySubscriptionsController::class, 'index']); // OK

        // Show Detail User Subscription id
        Route::get('/detail/{id}', [HistorySubscriptionsController::class, 'show']); // OK

        // Insert
        Route::post('/create', [HistorySubscriptionsController::class, 'store']); // OK

        // Update
        Route::post('/update/{id}', [HistorySubscriptionsController::class, 'update']); // OK

        // Delete
        Route::delete('/delete/{id}', [HistorySubscriptionsController::class, 'destroy']); // OK

        // Check Expired atau Belum subs nya
        Route::get('/check-expired/{id}', [HistorySubscriptionsController::class, 'checkExpired']); // OK
    });
});
