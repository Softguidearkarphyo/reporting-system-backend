<?php

namespace App\Http\Controllers\TaskPerformance;

use App\Utility;
use App\ReturnMessage;
use App\Models\TaskPerformance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\TaskPerformanceCreateRequest;

class TaskPerformanceController extends Controller
{
    public function create(TaskPerformanceCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            if ($data['create_array']) {
                $insertData = $data['create_array'];
                TaskPerformance::insert($insertData);
                DB::commit();
            }
            return response()->json([]);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("TaskPerformanceController::create", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
}
