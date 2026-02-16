<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ChatController;

Route::middleware('auth:sanctum')->group(function () {
    
    // User Info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Contract Lifecycle
    Route::post('/contracts/{type}/{id}/start', [ContractController::class, 'start']);
    Route::post('/contracts/{type}/{id}/finish', [ContractController::class, 'finish']);
    Route::post('/contracts/{type}/{id}/confirm', [ContractController::class, 'confirm']);
    Route::post('/contracts/{type}/{id}/cancel', [ContractController::class, 'cancel']);
    Route::post('/contracts/{type}/{id}/dispute', [ContractController::class, 'dispute']);

    // Review System
    Route::post('/contracts/{type}/{id}/review', [ReviewController::class, 'store']);
    Route::get('/experts/{id}/reviews', [ReviewController::class, 'getExpertReviews']);
    Route::get('/experts/{id}/rating-distribution', [ReviewController::class, 'getRatingDistribution']);

    // Chat System (API)
    Route::get('/chats', [ChatController::class, 'index']);
    Route::get('/chats/{id}', [ChatController::class, 'show']);
    Route::post('/chats/{id}/messages', [ChatController::class, 'store']);

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [App\Http\Controllers\Api\NotificationController::class, 'destroy']);
});
