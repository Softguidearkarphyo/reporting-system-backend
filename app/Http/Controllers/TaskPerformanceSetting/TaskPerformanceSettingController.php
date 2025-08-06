<?php

namespace App\Http\Controllers\TaskPerformanceSetting;

use App\Utility;
use App\ReturnMessage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\TaskPerformanceSetting;
use App\Http\Requests\Reporting\TaskPerformanceSettingSaveRequest;
use App\Http\Requests\Reporting\TaskPerformanceSettingeDiscardRequest;

class TaskPerformanceSettingController extends Controller
{
    public function save(TaskPerformanceSettingSaveRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            if ($data['create_array']) {
                $insertData = $data['create_array'];
                $keys = collect($insertData)
                    ->map(fn($item) => ['day' => $item['day'], 'staff_id' => $item['staff_id']])
                    ->unique()
                    ->values();
                foreach ($keys as $key) {
                    TaskPerformanceSetting::where('day', $key['day'])
                        ->where('staff_id', $key['staff_id'])->delete();
                };
                TaskPerformanceSetting::insert($insertData);
                DB::commit();
            }
            return response()->json([]);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("TaskPerformanceSettingController::create", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function discard(TaskPerformanceSettingeDiscardRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            if ($data['delete_array']) {
                $deleteData = $data['delete_array'];
                TaskPerformanceSetting::whereIn('staff_id', $deleteData)->delete();
                DB::commit();
            }
            return response()->json([]);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("TaskPerformanceSettingController::delete", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
}
