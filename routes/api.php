<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;


Route::prefix('reporting-system')->middleware('auth:sanctum')->group(function () {
    Route::get('/a', [AuthController::class, 'g']);
    Route::get('/b', [AuthController::class, 'K']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
