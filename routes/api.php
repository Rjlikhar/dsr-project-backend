<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DSRController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::controller(UserController::class)->group(function () {

    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', 'me'); 
        Route::post('/logout', 'logout');
        Route::put('/change-password', 'changePassword');
        Route::put('/save-app-password', 'saveAppPassword');
        Route::put(
        '/user/{id}/mentor-reviewer',
        [UserController::class, 'update']
    );
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/dsr', [DSRController::class, 'store']);
    Route::get('/dsr', [DSRController::class, 'show']);
    Route::get('/dsr/{id}', [DSRController::class, 'index']);
    Route::post('/dsr/send', [DSRController::class, 'submit']);
});

?>