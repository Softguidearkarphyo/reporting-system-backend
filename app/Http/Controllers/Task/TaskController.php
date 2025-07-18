<?php

namespace App\Http\Controllers\Task;

use App\Utility;
use App\Models\Task;
use App\ReturnMessage;
use App\Http\Controllers\Controller;
use App\Http\Resources\Reporting\TaskResource;
use App\Http\Requests\Reporting\TaskGetRequest;

class TaskController extends Controller
{
    public function get(TaskGetRequest $request)
    {
        try {
            $tasks  = Task::all();
            $tasks  = TaskResource::collection($tasks);
            return response()->json($tasks);
        } catch (\Throwable  $e) {
            Utility::log("TaskController::get", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
}
