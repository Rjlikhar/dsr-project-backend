<?php

use App\Http\Controllers\DSRController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MissedDsrController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(UserController::class)->group(function () {
    // Public routes
    Route::post('/login', 'login');
    Route::post('/forgot-password', 'forgotPassword')->middleware('throttle:5,1');
    Route::post('/reset-password', 'resetPassword')->middleware('throttle:10,1');

    // Login required
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/register', 'register');
        Route::get('/me', 'me');
        Route::post('/logout', 'logout');
        Route::put('/change-password', 'changePassword');
        Route::put('/save-app-password', 'saveAppPassword');
        Route::put('/user/{id}/mentor-reviewer', 'update');
        Route::get('/users', 'index');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/dsr', [DSRController::class, 'store']);
    Route::get('/dsr', [DSRController::class, 'show']);
    Route::get('/dsr/draft', [DSRController::class, 'index']);
    Route::post('/dsr/send', [DSRController::class, 'submit']);

    Route::post('/missed-dsr-requests', [MissedDsrController::class, 'store']);
    Route::get('/missed-dsr-notifications', [MissedDsrController::class, 'notification']);
    Route::get('/missed-dsr-requests', [MissedDsrController::class, 'getAllMissedDsrRequests']);
    Route::put('/missed-dsr-requests/{id}/status', [MissedDsrController::class, 'updateStatus']);

    Route::get('/groups', [GroupController::class, 'index']);
    Route::put('/user/group', [GroupController::class, 'update']);
});