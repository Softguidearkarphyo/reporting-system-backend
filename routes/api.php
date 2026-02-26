<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\Leave\LeaveController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\SystemManagementController;
use App\Http\Controllers\MenPower\MenPowerController;
use App\Http\Controllers\OverTime\OverTimeController;
use App\Http\Controllers\Reporting\ReportingController;
use App\Http\Controllers\SkillSheet\SkillSheetController;
use App\Http\Controllers\LeaveRecord\LeaveRecordController;
use App\Http\Controllers\TaskPerformance\TaskPerformanceController;
use App\Http\Controllers\TaskPerformanceSetting\TaskPerformanceSettingController;


Route::prefix('reporting-system')->middleware('auth:sanctum')->group(function () {
    Route::prefix('/staff')->group(function () {
        Route::get('/get', [StaffController::class, 'get']);
        Route::post('/create', [StaffController::class, 'create']);
        Route::post('/update', [StaffController::class, 'update']);
        Route::delete('/delete', [StaffController::class, 'delete']);
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
    Route::prefix('/task-performance-setting')->group(function () {
        Route::post('/save', [TaskPerformanceSettingController::class, 'save']);
        Route::post('/discard', [TaskPerformanceSettingController::class, 'discard']);
    });
    Route::prefix('/reporting')->group(function () {
        Route::post('/getAllHour', [ReportingController::class, 'getAllHour']);
    });
    Route::prefix('/project-hour')->group(function () {
        Route::post('/get', [ProjectController::class, 'getProjectHour']);
    });
    Route::prefix('/leave')->group(function () {
        Route::post('/get', [LeaveController::class, 'get']);
        Route::post('/create', [LeaveController::class, 'create']);
    });
    Route::prefix('/leave-record')->group(function () {
        Route::post('/get', [LeaveRecordController::class, 'get']);
        Route::post('/create', [LeaveRecordController::class, 'create']);
    });
    Route::prefix('/over-time')->group(function () {
        Route::post('/get', [OverTimeController::class, 'get']);
        Route::post('/create', [OverTimeController::class, 'create']);
    });
    Route::prefix('/fines')->group(function () {
        Route::post('/create', [StaffController::class, 'createFines']);
        Route::post('/get', [StaffController::class, 'getFines']);
        Route::post('/delete', [StaffController::class, 'deleteFines']);
        Route::post('/status-change', [StaffController::class, 'statusChange']);
    });
    Route::prefix('/men-power')->group(function () {
        Route::post('/get', [MenPowerController::class, 'get']);
    });
});

Route::get('/user', [AuthController::class, 'user']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
