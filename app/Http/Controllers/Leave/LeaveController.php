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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; 
use Carbon\Carbon;                      


class LeaveController extends Controller
{
  public function get(LeaveGetRequest $request)
{
    try {
        $data = $request->all();
        
        $query = Leave::with(['leave_records.staff']);

        $query->whereNull('deleted_at');

        if (!empty($data['id'])) {
            $query->where('id', $data['id']);
        }

        if (!empty($data['rec_id'])) {
            $query->where('rec_id', $data['rec_id']);
        }

        if (!empty($data['month']) && !empty($data['year'])) {
            $query->whereMonth('leave_date', $data['month'])
                  ->whereYear('leave_date', $data['year']);
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

                $existingLeaves = Leave::where('rec_id', $leaveRecord->id)
                    ->where('leave_date', $leaveDate->format('Y-m-d'))
                    ->get();

                if ($existingLeaves->isNotEmpty()) {
                    foreach ($existingLeaves as $exLeave) {
                        if ($exLeave->leave_type == 3) {
                            // Short Leave
                            $durInt = (int) $exLeave->duration;
                            $shortHoursMap = [
                                3 => 3.5, 4 => 3.0, 5 => 2.5, 
                                6 => 2.0, 7 => 1.5, 8 => 1.0, 9 => 0.5
                            ];
                            $deductHrs = $shortHoursMap[$durInt] ?? 0.5;
                            $leaveRecord->accumulated_hours = max(0, (float) $leaveRecord->accumulated_hours - $deductHrs);
                        } 
                        elseif ($exLeave->leave_type == 1) {
                            // Paid Normal Leave
                            $durInt = (int) $exLeave->duration;
                            $refundAmount = $durationMap[$durInt] ?? (float) $exLeave->day_count;

                            $leaveRecord->total_used = max(0, (float) $leaveRecord->total_used - $refundAmount);

                            if ($isFirstHalf) {
                                $leaveRecord->first_annual += $refundAmount;
                            } else {
                                $leaveRecord->second_annual += $refundAmount;
                            }
                        }
                        elseif ($exLeave->leave_type == 0) {
                            // Unpaid Leave Refund
                            $durInt = (int) $exLeave->duration;
                            $refundAmount = $durationMap[$durInt] ?? (float) $exLeave->day_count;
                            $leaveRecord->unpaid = max(0, (float) $leaveRecord->unpaid - $refundAmount);
                        }

                        $exLeave->delete();
                    }

                    $leaveRecord->remain_leaves = max(
                        0,
                        (float) $leaveRecord->carry_leaves + (float) $leaveRecord->first_annual + (float) $leaveRecord->second_annual
                    );
                    
                    $leaveRecord->save();
                }

                $carryLeaves  = max(0, (float) $leaveRecord->carry_leaves);
                $firstAnnual  = max(0, (float) $leaveRecord->first_annual);
                $secondAnnual = max(0, (float) $leaveRecord->second_annual);

                // Calculate total available balance including carry leaves
                if ($isFirstHalf) {
                    $availableBalance = $carryLeaves + $firstAnnual;
                } else {
                    $availableBalance = $carryLeaves + $secondAnnual + $firstAnnual;
                }

               
                if ($availableBalance <= 0) {
                    $leaveRecord->remain_leaves = 0;

                    if ($appliedDuration < 1.0) {
                        $additionalHours = $appliedDuration * 8.0; 
                        $leaveRecord->accumulated_hours += $additionalHours;
                        $leaveRecord->save();

                        $shortLeave = Leave::create([
                            "rec_id"     => $leaveRecord->id,
                            "leave_date" => $leaveDate->format('Y-m-d'),
                            "duration"   => (string) $durationId,
                            "reason"     => $data['reason'] ?? null,
                            "day_count"  => $appliedDuration,
                            "leave_type" => 3, // Short leave accumulating hours
                        ]);
                        $leaves[] = new LeaveResource($shortLeave);
                        continue;
                    } else {
                        $paidAmount   = 0.0;
                        $unpaidAmount = $appliedDuration;
                    }
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

                    if ($leaveRecord->carry_leaves > 0) {
                        $deductCarry = min($leaveRecord->carry_leaves, $remainingToDeduct);
                        $leaveRecord->carry_leaves -= $deductCarry;
                        $remainingToDeduct -= $deductCarry;
                    }

                    if ($remainingToDeduct > 0) {
                        if ($isFirstHalf) {
                            $leaveRecord->first_annual = max(0, (float) $leaveRecord->first_annual - $remainingToDeduct);
                        } else {
                            if ($leaveRecord->second_annual >= $remainingToDeduct) {
                                $leaveRecord->second_annual -= $remainingToDeduct;
                            } else {
                                $remainder = $remainingToDeduct - $leaveRecord->second_annual;
                                $leaveRecord->second_annual = 0;
                                $leaveRecord->first_annual = max(0, (float) $leaveRecord->first_annual - $remainder);
                            }
                        }
                    }

                    $leaveRecord->total_used += $paidAmount;
                    $leaveRecord->remain_leaves = max(
                        0,
                        (float) $leaveRecord->carry_leaves + (float) $leaveRecord->first_annual + (float) $leaveRecord->second_annual
                    );

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
                    $leaveRecord->unpaid += $unpaidAmount;
                    
                    // If duration is small and no balance remains, we can also accumulate hours
                    if ($appliedDuration < 1.0) {
                        $leaveRecord->accumulated_hours += ($appliedDuration * 8.0);
                    }

                    $leaveRecord->save();

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

   public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:leaves,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'validation_error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            $leave = Leave::where('id', $data['id'])
                ->whereNull('deleted_at')
                ->first();

            if (!$leave) {
                return response()->json([
                    'message' => 'Leave record not found or already deleted.'
                ], 404);
            }

            $leaveRecord = LeaveRecord::where('id', $leave->rec_id)
                ->lockForUpdate()
                ->first();

            if ($leaveRecord) {
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

                $shortHoursMap = [
                    3 => 3.5, 4 => 3.0, 5 => 2.5, 
                    6 => 2.0, 7 => 1.5, 8 => 1.0, 9 => 0.5
                ];

                $leaveDate = Carbon::parse($leave->leave_date);
                $leaveYear = (int) $leaveDate->format('Y');

                $firstPeriodStart = Carbon::createFromDate($leaveYear, 1, 1)->startOfDay();
                $firstPeriodEnd   = Carbon::createFromDate($leaveYear, 6, 30)->endOfDay();
                $isFirstHalf      = ($leaveDate >= $firstPeriodStart && $leaveDate <= $firstPeriodEnd);

                if ($leave->leave_type == 3) {
                    $durId = (int) $leave->duration;
                    $deductHrs = $shortHoursMap[$durId] ?? 0.5;

                    if ($leave->day_count <= 0) {
                        $leaveRecord->accumulated_hours = max(0, (float) $leaveRecord->accumulated_hours - $deductHrs);
                    }

                    if ($leave->day_count > 0) {
                        $leaveRecord->unpaid = max(0, (float) $leaveRecord->unpaid - (float) $leave->day_count);
                    }
                }
                elseif ($leave->leave_type == 1) {
                    // Paid Normal Leave
                    $durInt = (int) $leave->duration;
                    $refundAmount = $durationMap[$durInt] ?? (float) $leave->day_count;

                    $leaveRecord->total_used = max(0, (float) $leaveRecord->total_used - $refundAmount);

                    if ($isFirstHalf) {
                        $leaveRecord->first_annual += $refundAmount;
                    } else {
                        $leaveRecord->second_annual += $refundAmount;
                    }
                }
                elseif ($leave->leave_type == 0) {
                    // Unpaid Leave - Refund/Subtract from unpaid column
                    $durInt = (int) $leave->duration;
                    $refundAmount = $durationMap[$durInt] ?? (float) $leave->day_count;
                    $leaveRecord->unpaid = max(0, (float) $leaveRecord->unpaid - $refundAmount);
                }

                $leaveRecord->remain_leaves = max(
                    0,
                    (float) $leaveRecord->carry_leaves + (float) $leaveRecord->first_annual + (float) $leaveRecord->second_annual
                );

                $leaveRecord->save();
            }

            $leave->update(['deleted_at' => now()]);

            DB::commit();

            return response()->json([
                'message' => 'Leave deleted successfully',
                'id' => $data['id']
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Utility::log("LeaveController::delete", $e->getMessage());
            return response()->json([
                'message' => 'Server Error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}