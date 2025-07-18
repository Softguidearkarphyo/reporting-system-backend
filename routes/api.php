<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Employee\EmployeeController;


Route::prefix('reporting-system')->middleware('auth:sanctum')->group(function () {
    Route::prefix('/staff')->group(function () {
        Route::post('/get', [StaffController::class, 'get']);
        Route::post('/create', [StaffController::class, 'create']);
        Route::post('/update', [StaffController::class, 'update']);
        Route::post('/delete', [StaffController::class, 'delete']);
    });
    Route::prefix('/project')->group(function () {
        Route::post('/get', [ProjectController::class, 'get']);
        Route::post('/create', [ProjectController::class, 'create']);
        Route::post('/update', [ProjectController::class, 'update']);
        Route::post('/delete', [ProjectController::class, 'delete']);
    });
    Route::prefix('/employee')->group(function () {
        Route::post('/get-skill', [EmployeeController::class, 'get']);
        Route::post('/add-skill', [EmployeeController::class, 'create']);
    });
    Route::prefix('/task')->group(function () {
        Route::post('/get', [TaskController::class, 'get']);
    });
});

Route::get('/user', [AuthController::class, 'user']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
