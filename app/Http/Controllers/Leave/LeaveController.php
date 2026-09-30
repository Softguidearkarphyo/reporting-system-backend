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
        $data = $request->all();

        $query = Leave::with(['leave_records.staff']);

        if (!empty($data['id'])) {
            $query->where('id', $data['id']);
        }

        if (!empty($data['rec_id'])) {
            $query->where('rec_id', $data['rec_id']);
        }

        $leaves = $query->get();
        $leaves = LeaveResource::collection($leaves);

        return response()->json($leaves);
    } catch (\Throwable $e) {
        Utility::log("LeaveController::get", $e->getMessage());
        
        return response()->json([
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine()
        ], ReturnMessage::INTERNAL_SERVER_ERROR);
    }
}

public function create(LeaveCreateRequest $request)
{
    DB::beginTransaction();

    try {
        $data = $request->validated();
        $leaves = [];

        $durationMap = [
            1 => 1.0,      // Full day (8 Hours)
            2 => 0.5,      // Half day (4 Hours)
            3 => 0.4375,   // 3 Hrs 30 Min
            4 => 0.375,    // 3 Hours
            5 => 0.3125,   // 2 Hrs 30 Min
            6 => 0.25,     // 2 Hours
            7 => 0.1875,   // 1 Hr 30 Min
            8 => 0.125,    // 1 Hour
            9 => 0.0625,   // 30 Minutes
        ];

            $stringToDurationId = [
                // ID 1 (1.0 Day / 8 Hours)
                'Full Day'    => 1,
                '1 Day'       => 1,
                '8 Hours'     => 1,
                '8 Hrs'       => 1,

                // ID 2 (0.5 Day / 4 Hours)
                'Half Day'    => 2,
                '0.5 Day'     => 2,
                '4 Hours'     => 2,
                '4 Hrs'       => 2,

                // ID 3 (0.4375 Day / 3 Hrs 30 Min)
                '3.5 Hours'   => 3,
                '3 Hrs 30 Min' => 3,

                // ID 4 (0.375 Day / 3 Hours)
                '3 Hours'     => 4,
                '3 Hrs'       => 4,

                // ID 5 (0.3125 Day / 2 Hrs 30 Min)
                '2.5 Hours'   => 5,
                '2 Hrs 30 Min' => 5,

                // ID 6 (0.25 Day / 2 Hours)
                '2 Hours'     => 6,
                '2 Hrs'       => 6,

                // ID 7 (0.1875 Day / 1 Hr 30 Min)
                '1.5 Hours'   => 7,
                '1 Hr 30 Min' => 7,

                // ID 8 (0.125 Day / 1 Hour)
                '1 Hour'      => 8,
                '1 Hr'        => 8,

                // ID 9 (0.0625 Day / 30 Minutes)
                '0.5 Hour'    => 9,
                '30 Minutes'  => 9,
                '30 Mins'     => 9,
            ];
        $requestedDuration = $data['duration'] ?? 1;
        if (is_numeric($requestedDuration) && isset($durationMap[(int)$requestedDuration])) {
            $durationId = (int) $requestedDuration;
        } elseif (isset($stringToDurationId[$requestedDuration])) {
            $durationId = $stringToDurationId[$requestedDuration];
        } else {
            $floatVal = (float) $requestedDuration;
            $durationId = $getDurationId($floatVal, 1); 
        }

        $getDurationId = function ($amount, $fallbackId) use ($durationMap) {
            foreach ($durationMap as $id => $val) {
                if (abs($val - $amount) < 0.0001) {
                    return $id;
                }
            }
            return $fallbackId;
        };

        $datesToProcess = [];
        if (!empty($data['multi_date']) && is_array($data['multi_date'])) {
            $datesToProcess = array_map(fn($d) => Carbon::parse($d), $data['multi_date']);
        } elseif (!empty($data['start_date']) && is_array($data['start_date'])) {
            $datesToProcess = array_map(fn($d) => Carbon::parse($d), $data['start_date']);
        } elseif (!empty($data['leave_date'])) {
            $datesToProcess = [Carbon::parse($data['leave_date'])];
        } else {
            throw new \Exception("Leave date, multi_date, or start_date is required.");
        }

        $appliedDuration = $durationMap[$durationId] ?? 1.0;

        foreach ($datesToProcess as $leaveDate) {
            $leaveYear = (int) $leaveDate->format('Y');

            $leaveRecord = LeaveRecord::where('staff_id', $data['staff_id'])
                ->where('year', $leaveYear)
                ->first();

            if (!$leaveRecord) {
                throw new \Exception("Leave record for year {$leaveYear} not found.");
            }

            $firstPeriodStart = Carbon::createFromDate($leaveYear, 1, 1)->startOfDay();
            $firstPeriodEnd   = Carbon::createFromDate($leaveYear, 6, 30)->endOfDay();
            $isFirstHalf      = ($leaveDate >= $firstPeriodStart && $leaveDate <= $firstPeriodEnd);

            // Calculate total available balance based on period
            if ($isFirstHalf) {
                // In H1: Can ONLY use H1 balance
                $availableBalance = (float) $leaveRecord->first_annual;
            } else {
                // In H2: Can use H2 balance + ANY rollover balance left in H1
                $availableBalance = (float) $leaveRecord->second_annual + max(0, (float) $leaveRecord->first_annual);
            }

            if ($availableBalance >= $appliedDuration) {
                // CASE 1: Full Paid Leave
                $paidAmount   = $appliedDuration;
                $unpaidAmount = 0.0;
            } elseif ($availableBalance > 0) {
                // CASE 2: Split Leave (Paid + Unpaid)
                $paidAmount   = $availableBalance;
                $unpaidAmount = $appliedDuration - $availableBalance;
            } else {
                // CASE 3: Full Unpaid Leave
                $paidAmount   = 0.0;
                $unpaidAmount = $appliedDuration;
            }

            // --- 1. Process Paid Portion ---
            if ($paidAmount > 0) {
                if ($isFirstHalf) {
                    // H1 leaves only deduct from first_annual
                    $leaveRecord->first_annual -= $paidAmount;
                } else {
                    // H2 leaves: Deduct from second_annual first, then spill over into remaining first_annual
                    if ($leaveRecord->second_annual >= $paidAmount) {
                        $leaveRecord->second_annual -= $paidAmount;
                    } else {
                        $remainderToDeduct = $paidAmount - $leaveRecord->second_annual;
                        $leaveRecord->second_annual = 0;
                        $leaveRecord->first_annual -= $remainderToDeduct;
                    }
                }

                $leaveRecord->total_used += $paidAmount;
                $leaveRecord->remain_leaves = $leaveRecord->first_annual + $leaveRecord->second_annual;
                $leaveRecord->save();

                $paidDurationId = $getDurationId($paidAmount, $durationId);

                $paidLeave = Leave::create([
                    "rec_id"     => $leaveRecord->id,
                    "leave_date" => $leaveDate->format('Y-m-d'),
                    "duration"   => $paidDurationId,
                    "reason"     => $data['reason'] ?? null,
                    "day_count"  => $paidAmount,
                    "leave_type" => 1, // Paid
                ]);
                $leaves[] = new LeaveResource($paidLeave);
            }

            if ($unpaidAmount > 0) {
                $unpaidDurationId = $getDurationId($unpaidAmount, $durationId);

                $unpaidLeave = Leave::create([
                    "rec_id"     => $leaveRecord->id,
                    "leave_date" => $leaveDate->format('Y-m-d'),
                    "duration"   => $unpaidDurationId,
                    "reason"     => $data['reason'] ?? null,
                    "day_count"  => $unpaidAmount,
                    "leave_type" => 0, // Unpaid
                ]);
                $leaves[] = new LeaveResource($unpaidLeave);
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Leave(s) processed successfully',
            'data'    => $leaves
        ], 201);

    } catch (\Throwable $e) {
        DB::rollBack();
        Utility::log("LeaveController::create", $e->getMessage());

        return response()->json([
            'status' => ReturnMessage::INTERNAL_SERVER_ERROR,
            'error'  => $e->getMessage()
        ], 500);
    }
}
}
