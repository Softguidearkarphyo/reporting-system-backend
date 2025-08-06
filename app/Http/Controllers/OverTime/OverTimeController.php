<?php

namespace App\Http\Controllers\OverTime;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use App\Http\Requests\OverTime\OverTimeCreateRequest;
use App\Http\Requests\OverTime\OverTimeGetRequest;
use App\ReturnMessage;
use App\Http\Resources\OverTime\OverTimeResource;
use App\Models\OverTime;
use Illuminate\Support\Facades\DB;
use App\Utility;

class OverTimeController extends Controller
{
    public function get(OverTimeGetRequest $request)
    {
        try {
            $data   = $request->all();
            $query = OverTime::with('staff');
            if (!empty($data['id'])) {
                $query->where('id', $data['id']);
            }
            $overtime = $query->get();
            $overtime = OverTimeResource::collection($overtime);
            return response()->json($overtime);
        } catch (\Throwable  $e) {
            Utility::log("OverTimeController::get", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function create(OverTimeCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $createData = [
                "staff_id"    => $data['staff_id'],
                'ot_date'     => $data['ot_date'],
                'ot_time'     => $data['ot_time'],
            ];
            $ot = new OverTimeResource(OverTime::create($createData));
            DB::commit();
            return response()->json($ot);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("OverTimeController::create", $e->getMessage());
            return ["status" => ReturnMessage::INTERNAL_SERVER_ERROR];
        }
    }
}
