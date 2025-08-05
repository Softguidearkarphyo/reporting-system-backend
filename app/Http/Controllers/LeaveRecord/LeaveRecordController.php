<?php

namespace App\Http\Controllers\LeaveRecord;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRecord\LeaveRecordCreateRequest;
use App\Http\Requests\LeaveRecord\LeaveRecordGetRequest;
use App\Http\Resources\LeaveRecord\LeaveRecordResource;
use App\Models\LeaveRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\ReturnMessage;
use App\Utility;

class LeaveRecordController extends Controller
{

    public function get(LeaveRecordGetRequest $request)
    {
        try {
            $data   = $request->all();
            $query = LeaveRecord::with('staff');
            if (!empty($data['id'])) {
                $query->where('id', $data['id']);
            }
            $leaveRecord = $query->get();
            $leaveRecord = LeaveRecordResource::collection($leaveRecord);
            return response()->json($leaveRecord);
        } catch (\Throwable  $e) {
            Utility::log("LeaveRecordController::get", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function create(LeaveRecordCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $createData = [
                'staff_id'         => $data['staff_id'],
                'permanent_date'   => $data['permanent_date'],
                'remain_leaves'    => $data['remain_leaves'],
                'total_leaves'     => $data['total_leaves'],
            ];
            $leaveRecord = new LeaveRecordResource(LeaveRecord::create($createData));
            DB::commit();
            return response()->json($leaveRecord);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("LeaveRecordController::create", $e->getMessage());
            return ["status" => ReturnMessage::INTERNAL_SERVER_ERROR];
        }
    }
}
