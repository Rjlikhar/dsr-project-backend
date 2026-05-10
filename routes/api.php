<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DSRController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MissedDsrController;

Route::controller(UserController::class)->group(function () {

    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', 'me'); 
        Route::post('/logout', 'logout');
        Route::put('/change-password', 'changePassword');
        Route::put('/save-app-password', 'saveAppPassword');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/dsr', [DSRController::class, 'store']);
    Route::get('/dsr', [DSRController::class, 'show']);
    Route::get('/dsr/{id}', [DSRController::class, 'index']);
    Route::put('/dsr/{id}', [DSRController::class, 'update']);
    Route::post('/dsr/send', [DSRController::class, 'submit']);
});


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/missed-dsr-requests',[MissedDsrController::class, 'store']);
    Route::get('/missed-dsr-notifications',[MissedDsrController::class, 'notification']);
    
    Route::middleware('admin')->group(function () {
        Route::get('/missed-dsr-requests',[MissedDsrController::class, 'getAllMissedDsrRequests']);
        Route::put('/missed-dsr-requests/{id}/status',[MissedDsrController::class, 'updateStatus']);
    });
});

?>