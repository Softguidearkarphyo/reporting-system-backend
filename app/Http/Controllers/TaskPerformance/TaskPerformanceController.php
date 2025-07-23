<?php

namespace App\Http\Controllers\TaskPerformance;

use App\Utility;
use App\ReturnMessage;
use App\Models\TaskPerformance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\TaskPerformanceCreateRequest;
use App\Http\Requests\Reporting\TaskPerformanceDeleteRequest;

class TaskPerformanceController extends Controller
{
    public function create(TaskPerformanceCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            if ($data['create_array']) {
                $insertData = $data['create_array'];
                TaskPerformance::upsert($insertData, ['date', 'staff_id', 'period'], ['project_id', 'task_id']);
                DB::commit();
            }
            return response()->json([]);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("TaskPerformanceController::create", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(TaskPerformanceDeleteRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            if ($data['delete_array']) {
                $deleteData = $data['delete_array'];
                foreach ($deleteData as $deleteDatum) {
                    TaskPerformance::where('date', $deleteDatum['date'])
                        ->where('staff_id', $deleteDatum['staff_id'])
                        ->where('period', $deleteDatum['period'])->delete();
                };
                DB::commit();
            }
            return response()->json([]);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("TaskPerformanceController::delete", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
}
