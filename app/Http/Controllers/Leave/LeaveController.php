<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\LeaveCreateRequest;
use App\Http\Requests\Leave\LeaveGetRequest;
use App\Http\Resources\Leave\LeaveResource;
use App\Models\Leave;
use App\Models\LeaveRecord;
use Illuminate\Support\Facades\DB;
use App\ReturnMessage;
use Illuminate\Http\Request;
use App\Utility;
use Carbon\Carbon;
use DateTime;

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
            $leaves = [];

            if (isset($data['start_date'])) {
                $getDates =  $data['start_date'];
                $carbonDates = array_map(fn($d) => Carbon::parse($d), $getDates);
                $startDate = min($carbonDates);
                $leaveYear = (int)$startDate->format('Y');
                $firstPeriodStart = new DateTime("$leaveYear-01-01");
                $firstPeriodEnd = (clone $firstPeriodStart)->modify('+6 months');
                $leaveRecord = LeaveRecord::where('staff_id', $data['staff_id'])->latest()->first();

                // 1 = paid & 0 = unpaid
                foreach ($carbonDates as $date) {
                    if ($startDate >= $firstPeriodStart && $startDate < $firstPeriodEnd) {
                        if ($leaveRecord->first_annual > 0) {
                            $leaveRecord->first_annual--;
                            $status = 1;
                        } else {
                            $leaveRecord->first_annual--;
                            $status = 0;
                        }
                    } else {
                        if ($leaveRecord->second_annual > 0) {
                            $leaveRecord->second_annual--;
                            $status = 1;
                        } else {
                            $leaveRecord->second_annual--;
                            $status = 0;
                        }
                    }
                    $leaveRecord->total_used++;
                    $leaveRecord->remain_leaves = $leaveRecord->first_annual + $leaveRecord->second_annual;
                    $leaveRecord->update();
                    $createData = [
                        "rec_id"     => $leaveRecord->id,
                        "leave_date" => $date->format('Y-m-d'),
                        "duration"   => $data['duration'],
                        "reason"     => $data['reason'] ?? null,
                        "day_count"  => 1,
                        "leave_type" => $status,
                    ];
                    $leave = Leave::create($createData);
                    $leaves[] = new LeaveResource($leave);
                }
            } else {
                if (!isset($data['leave_date'])) {
                    throw new \Exception("Leave date is required for single-day leave");
                }

                if (isset($data['leave_date'])) {
                    $leaveDate = new DateTime($data['leave_date']);
                    $leaveYear = (int)$leaveDate->format('Y');
                    $firstPeriodStart = new DateTime("$leaveYear-01-01");
                    $firstPeriodEnd = (clone $firstPeriodStart)->modify('+6 months');
                    $leaveRecord = LeaveRecord::where('staff_id', $data['staff_id'])->latest()->first();

                    $durationMap = [
                        1 => 1,
                        2 => 0.5,
                        3 => 0.4375,
                        4 => 0.375,
                        5 => 0.3125,
                        6 => 0.25,
                        7 => 0.1875,
                        8 => 0.125,
                        9 => 0.0625,
                    ];
                    $count = $durationMap[$data['duration']] ?? 0;

                    // 1 = paid & 0 = unpaid
                    if ($leaveDate >= $firstPeriodStart && $leaveDate < $firstPeriodEnd) {
                        $update['first_annual'] = $leaveRecord->first_annual - $count;
                        $status = ($leaveRecord->first_annual > 0) ? 1 : 0;
                    } else {
                        $update['second_annual'] = $leaveRecord->second_annual - $count;
                        $status = ($leaveRecord->second_annual > 0) ? 1 : 0;
                    }
                    if (!empty($update)) {
                        $leaveRecord->total_used += $count;
                        $leaveRecord->remain_leaves = $leaveRecord->first_annual + $leaveRecord->second_annual;
                        $leaveRecord->update($update);
                    }

                    $createData = [
                        "rec_id"      => $leaveRecord->id,
                        "leave_date"  => $data['leave_date'],
                        "duration"    => $data['duration'] ?? null,
                        "reason"      => $data['reason'] ?? null,
                        "day_count"   => 1,
                        "leave_type"  => $status,
                    ];
                    $leave = Leave::create($createData);
                    $leaves[] = new LeaveResource($leave);
                }
            }
            DB::commit();
            return response()->json([
                'message' => 'Leave(s) created successfully',
                'data'    => $leaves
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Utility::log("LeaveController::create", $e->getMessage());

            return response()->json([
                'status' => ReturnMessage::INTERNAL_SERVER_ERROR,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
