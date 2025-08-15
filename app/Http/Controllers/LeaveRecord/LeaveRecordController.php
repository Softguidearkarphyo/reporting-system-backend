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
use DateTime;

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
            if (isset($data['permanent_date'])) {
                $permanent_date = new DateTime($data['permanent_date']);
                $currentYear = (int)$permanent_date->format('Y');
                $firstPeriodStart = new DateTime("$currentYear-01-01");
                $yearEnd = new DateTime("$currentYear-12-31");
                $firstPeriodEnd = (clone $firstPeriodStart)->modify('+6 months');
                $totalDays = (int)$yearEnd->diff($firstPeriodStart)->format('%a') + 1;
                $remainingDays = (int)$yearEnd->diff($permanent_date)->format('%a') + 1;
                $totalLeave = 10;
                $leave = round(($remainingDays / $totalDays) * $totalLeave * 2) / 2;
                $firstHalfLeave = 0;
                if ($permanent_date <= $firstPeriodEnd) {
                    $totalFirstHalfDays = (int)$firstPeriodEnd->diff($firstPeriodStart)->format('%a') + 1;
                    $remainingFirstHalfDays = (int)$firstPeriodEnd->diff($permanent_date)->format('%a') + 1;
                    $firstHalfLeave = round(($remainingFirstHalfDays / $totalFirstHalfDays) * ($totalLeave / 2) * 2) / 2;
                }
                $secondHalfLeave = 0;
                if ($permanent_date > $firstPeriodEnd) {
                    $remainingSecondHalfDays = (int)$yearEnd->diff($permanent_date)->format('%a') + 1;
                    $secondHalfLeave = round(($remainingSecondHalfDays / ($totalDays / 2)) * ($totalLeave / 2) * 2) / 2;
                } else {
                    $secondHalfLeave = $totalLeave / 2;
                }
            }
            $createData = [
                'staff_id'        => $data['staff_id'],
                'permanent_date'  => $data['permanent_date'],
                'remain_leaves'   => $leave,
                'total_leaves'    => $leave,
                'first_annual'    => $firstHalfLeave,
                'second_annual'   => $secondHalfLeave,
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
