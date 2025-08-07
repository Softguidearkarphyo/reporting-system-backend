<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\TaskGetRequest;
use App\Http\Resources\Reporting\WorkHourResource;
use App\Models\TaskPerformance;
use App\Utility;
use App\ReturnMessage;

class ReportingController extends Controller
{

    public function getAllHour(TaskGetRequest $request)
    {
        try {
            $data = $request->all();
            $start = min($data['start_date'], $data['end_date']);
            $end = max($data['start_date'], $data['end_date']);
            $query = TaskPerformance::whereHas('staff')->whereBetween('date', [$start, $end]);
            $reporting = $query->get();
            $reporting = WorkHourResource::collection($reporting);
            return response()->json($reporting);
        } catch (\Throwable $e) {
            Utility::log("ReportingController::getAllHour", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
}
