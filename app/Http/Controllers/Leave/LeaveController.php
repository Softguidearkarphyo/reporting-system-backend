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
            'Full Day'  => 1,
            'Half Day'  => 2,
            '1 Day'     => 1,
            '0.5 Day'   => 2,
        ];

        $requestedDuration = $data['duration'] ?? 1;
        if (is_numeric($requestedDuration)) {
            $durationId = (int) $requestedDuration;
        } else {
            $durationId = $stringToDurationId[$requestedDuration] ?? 1;
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

            $availableBalance = $isFirstHalf ? $leaveRecord->first_annual : $leaveRecord->second_annual;

            if ($availableBalance >= $appliedDuration) {
                // CASE 1: Full Paid Leave
                $paidAmount   = $appliedDuration;
                $unpaidAmount = 0.0;
            } elseif ($availableBalance > 0) {
                // CASE 2: Split Leave (e.g., Paid + Unpaid)
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
                    $leaveRecord->first_annual -= $paidAmount;
                } else {
                    $leaveRecord->second_annual -= $paidAmount;
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
