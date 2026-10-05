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
use App\Utility;
use Carbon\Carbon;

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
            return response()->json(LeaveResource::collection($leaves));
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

        // Duration mappings
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
            'Full Day' => 1, '1 Day' => 1, '8 Hours' => 1, '8 Hrs' => 1,
            'Half Day' => 2, '0.5 Day' => 2, '4 Hours' => 2, '4 Hrs' => 2,
            '3.5 Hours' => 3, '3 Hrs 30 Min' => 3,
            '3 Hours' => 4, '3 Hrs' => 4,
            '2.5 Hours' => 5, '2 Hrs 30 Min' => 5,
            '2 Hours' => 6, '2 Hrs' => 6,
            '1.5 Hours' => 7, '1 Hr 30 Min' => 7,
            '1 Hour' => 8, '1 Hr' => 8,
            '0.5 Hour' => 9, '30 Minutes' => 9, '30 Mins' => 9,
        ];

        $getDurationId = function ($amount, $fallbackId) use ($durationMap) {
            foreach ($durationMap as $id => $val) {
                if (abs($val - $amount) < 0.0001) {
                    return $id;
                }
            }
            return $fallbackId;
        };

        $requestedDuration = $data['duration'] ?? 1;
        if (is_numeric($requestedDuration) && isset($durationMap[(int)$requestedDuration])) {
            $durationId = (int) $requestedDuration;
        } elseif (isset($stringToDurationId[$requestedDuration])) {
            $durationId = $stringToDurationId[$requestedDuration];
        } else {
            $floatVal = (float) $requestedDuration;
            $durationId = $getDurationId($floatVal, 1);
        }

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
                ->lockForUpdate()
                ->first();

            if (!$leaveRecord) {
                throw new \Exception("Leave record for year {$leaveYear} not found.");
            }

            $firstPeriodStart = Carbon::createFromDate($leaveYear, 1, 1)->startOfDay();
            $firstPeriodEnd   = Carbon::createFromDate($leaveYear, 6, 30)->endOfDay();
            $isFirstHalf      = ($leaveDate >= $firstPeriodStart && $leaveDate <= $firstPeriodEnd);

            $carryLeaves = max(0, (float) $leaveRecord->carry_leaves);
            $firstAnnual = max(0, (float) $leaveRecord->first_annual);
            $secondAnnual = max(0, (float) $leaveRecord->second_annual);

            // Calculate total available balance including carry leaves
            if ($isFirstHalf) {
                $availableBalance = $carryLeaves + $firstAnnual;
            } else {
                $availableBalance = $carryLeaves + $secondAnnual + $firstAnnual;
            }

            if ($availableBalance >= $appliedDuration) {
                $paidAmount   = $appliedDuration;
                $unpaidAmount = 0.0;
            } elseif ($availableBalance > 0) {
                $paidAmount   = $availableBalance;
                $unpaidAmount = $appliedDuration - $availableBalance;
            } else {
                $paidAmount   = 0.0;
                $unpaidAmount = $appliedDuration;
            }

            // Deduct Paid Portion with Priority: Carry Leaves -> Second Annual -> First Annual
            if ($paidAmount > 0) {
                $remainingToDeduct = $paidAmount;

                // 1. Deduct from Carry Leaves first
                if ($leaveRecord->carry_leaves > 0) {
                    $deductCarry = min($leaveRecord->carry_leaves, $remainingToDeduct);
                    $leaveRecord->carry_leaves -= $deductCarry;
                    $remainingToDeduct -= $deductCarry;
                }

                // 2. Deduct remaining from Annual Leaves
                if ($remainingToDeduct > 0) {
                    if ($isFirstHalf) {
                        $leaveRecord->first_annual = max(0, (float) $leaveRecord->first_annual - $remainingToDeduct);
                    } else {
                        // In second half, consume second_annual first, then fallback to first_annual
                        if ($leaveRecord->second_annual >= $remainingToDeduct) {
                            $leaveRecord->second_annual -= $remainingToDeduct;
                        } else {
                            $remainder = $remainingToDeduct - $leaveRecord->second_annual;
                            $leaveRecord->second_annual = 0;
                            $leaveRecord->first_annual = max(0, (float) $leaveRecord->first_annual - $remainder);
                        }
                    }
                }

                // Update totals and remain_leaves
                $leaveRecord->total_used += $paidAmount;
                $leaveRecord->remain_leaves = max(
                    0,
                    (float) $leaveRecord->carry_leaves + (float) $leaveRecord->first_annual + (float) $leaveRecord->second_annual
                );

                // If balance is depleted, convert active short leaves to unpaid
                if ($leaveRecord->remain_leaves <= 0) {
                    $leaveRecord->remain_leaves = 0;
                    $leaveRecord->accumulated_hours = 0.0;

                    $existingShortLeaves = $leaveRecord->leaves()->where('leave_type', 3)->get();
                    foreach ($existingShortLeaves as $sl) {
                        $durInt = (int) $sl->duration;
                        $dayCountVal = $durationMap[$durInt] ?? 0.0625;
                        $sl->update([
                            'leave_type' => 0, // Unpaid
                            'day_count'  => $dayCountVal,
                            'reason'     => ($sl->reason ? $sl->reason . ' ' : '') . '(Converted to Unpaid - Leave balance depleted)',
                        ]);
                    }
                }

                $leaveRecord->save();

                $paidDurationId = $getDurationId($paidAmount, $durationId);

                $paidLeave = Leave::create([
                    "rec_id"     => $leaveRecord->id,
                    "leave_date" => $leaveDate->format('Y-m-d'),
                    "duration"   => (string) $paidDurationId,
                    "reason"     => $data['reason'] ?? null,
                    "day_count"  => $paidAmount,
                    "leave_type" => 1, // Paid
                ]);
                $leaves[] = new LeaveResource($paidLeave);
            }

            // Process Unpaid Portion
            if ($unpaidAmount > 0) {
                if ($leaveRecord->remain_leaves <= 0) {
                    $leaveRecord->remain_leaves = 0;
                    $leaveRecord->accumulated_hours = 0.0;

                    $existingShortLeaves = $leaveRecord->leaves()->where('leave_type', 3)->get();
                    foreach ($existingShortLeaves as $sl) {
                        $durInt = (int) $sl->duration;
                        $dayCountVal = $durationMap[$durInt] ?? 0.0625;
                        $sl->update([
                            'leave_type' => 0, // Unpaid
                            'day_count'  => $dayCountVal,
                            'reason'     => ($sl->reason ? $sl->reason . ' ' : '') . '(Converted to Unpaid - Leave balance depleted)',
                        ]);
                    }

                    $leaveRecord->save();
                }

                $unpaidDurationId = $getDurationId($unpaidAmount, $durationId);

                $unpaidLeave = Leave::create([
                    "rec_id"     => $leaveRecord->id,
                    "leave_date" => $leaveDate->format('Y-m-d'),
                    "duration"   => (string) $unpaidDurationId,
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