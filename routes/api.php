<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Staff\StaffController;


Route::prefix('reporting-system')->middleware('auth:sanctum')->group(function () {
    Route::prefix('/staff')->group(function () {
        Route::post('/get', [StaffController::class, 'get']);
        Route::post('/create', [StaffController::class, 'create']);
        Route::post('/update', [StaffController::class, 'update']);
        Route::post('/delete', [StaffController::class, 'delete']);
    });
});

Route::get('/user', [AuthController::class, 'user']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
