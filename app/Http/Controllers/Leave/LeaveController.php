<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\LeaveCreateRequest;
use App\Http\Requests\Leave\LeaveGetRequest;
use App\Http\Resources\Leave\LeaveResource;
use App\Models\Leave;
use Illuminate\Support\Facades\DB;
use App\ReturnMessage;
use Illuminate\Http\Request;
use App\Utility;

class LeaveController extends Controller
{
    public function get(LeaveGetRequest $request)
    {
        try {
            $data   = $request->all();
            $query = Leave::with('staff');
            if (!empty($data['id'])) {
                $query->where('id', $data['id']);
            }
            $leaves = $query->get();
            $leaves = LeaveResource::collection($leaves);
            return response()->json($leaves);
        } catch (\Throwable  $e) {
            Utility::log("LeaveController::get", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
    public function create(LeaveCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $createData = [
                "staff_id"    => $data['staff_id'],
                'leave_type'  => $data['leave_type'],
                'start_date'  => $data['start_date'] ?? null,
                'end_date'    => $data['end_date'] ?? null,
                'leave_date'  => $data['leave_date'] ?? null,
                'duration'    => $data['duration'] ?? null,
                'reason'      => $data['reason'],
            ];
            $leave = new LeaveResource(Leave::create($createData));
            DB::commit();
            return response()->json($leave);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("LeaveController::create", $e->getMessage());
            return ["status" => ReturnMessage::INTERNAL_SERVER_ERROR];
        }
    }
}
