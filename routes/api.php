<?php

use App\Http\Controllers\SystemManagementController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\SkillSheet\SkillSheetController;
use App\Http\Controllers\TaskPerformance\TaskPerformanceController;
use App\Http\Controllers\Task\TaskController;
use Illuminate\Support\Facades\Route;


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
    Route::prefix('/employee-skill-sheet')->group(function () {
        Route::post('/get', [SkillSheetController::class, 'get']);
        Route::post('/create', [SkillSheetController::class, 'create']);
        Route::post('/update', [SkillSheetController::class, 'update']);
    });
    Route::prefix('/sys-management')->group(function () {
        Route::post('/get-tech-stack', [SystemManagementController::class, 'getTechStack']);
        Route::post('/get-grade', [SystemManagementController::class, 'getGrade']);
        Route::post('/get-position', [SystemManagementController::class, 'getPosition']);
        Route::post('/get-japanese-level', [SystemManagementController::class, 'getJapaneseLevel']);
        Route::post('/get-proficiency-level', [SystemManagementController::class, 'getProficiencyLevel']);
        Route::post('/get-responsibility', [SystemManagementController::class, 'getResponsibility']);
    });
    Route::prefix('/task')->group(function () {
        Route::post('/get', [TaskController::class, 'get']);
    });
    Route::prefix('/task-performance')->group(function () {
        Route::post('/create', [TaskPerformanceController::class, 'create']);
        Route::post('/delete', [TaskPerformanceController::class, 'delete']);
    });
});

Route::get('/user', [AuthController::class, 'user']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
