<?php

namespace App\Http\Controllers\MenPower;

use App\Utility;
use App\ReturnMessage;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\MenPower\MenPowerResource;
use App\Http\Requests\MenPower\MenPowerGetRequest;

class MenPowerController extends Controller
{
    public function get(MenPowerGetRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $menHours = Project::leftJoin('task_performance', function ($join) use ($data) {
                $join->on('projects.id', '=', 'task_performance.project_id')
                    ->whereBetween('task_performance.date', [$data['start_date'], $data['end_date']]);
            })
                ->select('projects.*')
                ->selectRaw('COUNT(DISTINCT task_performance.date) as days')
                ->selectRaw('COUNT(task_performance.id) * 0.5 as hours')
                ->selectRaw('COUNT(DISTINCT task_performance.staff_id) as men')
                ->groupBy('projects.id')
                ->get();
            $menHours = MenPowerResource::Collection($menHours);
            return response()->json($menHours);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("TaskPerformanceController::create", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
}
