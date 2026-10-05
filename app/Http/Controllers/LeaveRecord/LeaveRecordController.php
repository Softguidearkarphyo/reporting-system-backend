<?php

namespace App\Http\Controllers\LeaveRecord;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRecord\LeaveRecordCreateRequest;
use App\Http\Requests\LeaveRecord\LeaveRecordGetRequest;
use App\Http\Resources\LeaveRecord\LeaveRecordResource;
use App\Models\LeaveRecord;
use App\ReturnMessage;
use App\Utility;
use Carbon\Carbon; 
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveRecordController extends Controller
{
    public function get(LeaveRecordGetRequest $request)
    {
        try {
            $data = $request->all();

            $query = LeaveRecord::with(['staff', 'leaves']);

            if (!empty($data['id'])) {
                $query->where('id', $data['id']);
            }

            if (!empty($data['staff_id'])) {
                $query->where('staff_id', $data['staff_id']);
            }

            if (!empty($data['year'])) {
                $query->where('year', $data['year']);
            }

            $leaveRecords = $query->get();

            return response()->json(LeaveRecordResource::collection($leaveRecords), 200);

        } catch (\Throwable $e) {
            Utility::log("LeaveRecordController::get", $e->getMessage());

            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function create(LeaveRecordCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $permanentDate = Carbon::parse($data['permanent_date']);
            
            $targetYear = isset($data['year']) ? (int) $data['year'] : now()->year;
            $permanentYear = $permanentDate->year;

            if ($targetYear < $permanentYear) {
                return response()->json([
                    'error' => "Cannot create leave record for {$targetYear} prior to permanent date ({$permanentDate->toDateString()})."
                ], 422);
            }

            $roundToHalf = fn($value) => round($value * 2) / 2;

            if ($targetYear > $permanentYear) {
                $totalLeave      = 10.0;
                $firstHalfLeave  = 5.0;
                $secondHalfLeave = 5.0;
            } else {
                $yearStart = Carbon::createFromDate($targetYear, 1, 1)->startOfDay();
                $yearEnd   = Carbon::createFromDate($targetYear, 12, 31)->startOfDay();
                $midYear   = Carbon::createFromDate($targetYear, 6, 30)->startOfDay();
                $h2Start   = Carbon::createFromDate($targetYear, 7, 1)->startOfDay();

                $totalDaysInYear     = $yearStart->diffInDays($yearEnd) + 1;
                $remainingDaysInYear = $permanentDate->diffInDays($yearEnd) + 1;

                $totalLeave = $roundToHalf(($remainingDaysInYear / $totalDaysInYear) * 10);

                if ($permanentDate->lte($midYear)) {
                    $h1TotalDays     = $yearStart->diffInDays($midYear) + 1;
                    $h1RemainingDays = $permanentDate->diffInDays($midYear) + 1;

                    $firstHalfLeave  = $roundToHalf(($h1RemainingDays / $h1TotalDays) * 5);
                    $secondHalfLeave = 5.0;
                } else {
                    $h2TotalDays     = $h2Start->diffInDays($yearEnd) + 1;
                    $h2RemainingDays = $permanentDate->diffInDays($yearEnd) + 1;

                    $firstHalfLeave  = 0.0;
                    $secondHalfLeave = $roundToHalf(($h2RemainingDays / $h2TotalDays) * 5);
                }
            }

            $leaveRecord = LeaveRecord::withTrashed()
                ->where('staff_id', $data['staff_id'])
                ->where('year', $targetYear)
                ->first();

            $recordData = [
                'staff_id'          => $data['staff_id'],
                'year'              => $targetYear,
                'permanent_date'    => $data['permanent_date'],
                'remain_leaves'     => $totalLeave,
                'total_leaves'      => $totalLeave,
                'first_annual'      => $firstHalfLeave,
                'second_annual'     => $secondHalfLeave,
                'total_used'        => 0,
                'accumulated_hours' => 0.0,
            ];

            if ($leaveRecord) {
                if ($leaveRecord->trashed()) {
                    $leaveRecord->restore();
                }
                $leaveRecord->update($recordData);
            } else {
                $leaveRecord = LeaveRecord::create($recordData);
            }

            DB::commit();

            return response()->json(new LeaveRecordResource($leaveRecord), 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            Utility::log("LeaveRecordController::create", $e->getMessage());

            return response()->json([
                'error'   => 'Failed to create leave record',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function addShortLeave(Request $request)
{
    Log::info('Add Short Leave Triggered:', $request->all());

    $durationToHoursMap = [
        3 => 3.5, // 3.5 Hours
        4 => 3.0, // 3.0 Hours
        5 => 2.5, // 2.5 Hours
        6 => 2.0, // 2.0 Hours
        7 => 1.5, // 1.5 Hours
        8 => 1.0, // 1.0 Hour
        9 => 0.5, // 0.5 Hour (30 Mins)
    ];

    if ($request->has('duration')) {
        $requestedDuration = (int) $request->duration;
        $hours = $durationToHoursMap[$requestedDuration] ?? 0.5;

        $request->merge([
            'hours' => $hours
        ]);
    }

    $request->validate([
        'staff_id'   => 'required|exists:staffs,id',
        'leave_date' => 'nullable|date',
        'hours'      => 'required|numeric|min:0.5',
        'reason'     => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
        $currentLeaveDate = $request->leave_date ? Carbon::parse($request->leave_date) : now();
        $targetYear = (int) $currentLeaveDate->year;

        $record = LeaveRecord::lockForUpdate()->firstOrCreate(
            [
                'staff_id' => $request->staff_id,
                'year'     => $targetYear,
            ],
            [
                'carry_leaves'      => 0,
                'remain_leaves'     => 0,
                'first_annual'      => 0,
                'second_annual'     => 0,
                'total_used'        => 0,
                'total_leaves'      => 0,
                'accumulated_hours' => 0,
            ]
        );

        $convertShortLeavesToUnpaid = function () use ($record, $durationToHoursMap) {
            $shortLeaves = $record->leaves()->where('leave_type', 3)->get();
            foreach ($shortLeaves as $sl) {
                $durId = (int) $sl->duration;
                $hrs = $durationToHoursMap[$durId] ?? 0.5;
                $dayCount = $hrs / 8.0;

                $sl->update([
                    'leave_type' => 0, // Unpaid
                    'day_count'  => $dayCount,
                    'reason'     => ($sl->reason ? $sl->reason . ' ' : '') . '(Converted to Unpaid - Leave balance depleted)',
                ]);
            }
        };

        $addedHours = (float) $request->hours;
        $totalHours = (float) $record->accumulated_hours + $addedHours;

        if ($totalHours >= 4.0) {
            $daysToDeduct = floor($totalHours / 4.0) * 0.5; 
            $remainingHours = fmod($totalHours, 4.0);       

            $record->leaves()
                ->where('leave_type', 3)
                ->delete();

            $firstPeriodStart = Carbon::createFromDate($targetYear, 1, 1)->startOfDay();
            $firstPeriodEnd   = Carbon::createFromDate($targetYear, 6, 30)->endOfDay();
            $isFirstHalf      = ($currentLeaveDate >= $firstPeriodStart && $currentLeaveDate <= $firstPeriodEnd);

            // Fetch current balances (including carry_leaves)
            $carryBal  = max(0, (float) $record->carry_leaves);
            $firstBal  = max(0, (float) $record->first_annual);
            $secondBal = max(0, (float) $record->second_annual);

            if ($isFirstHalf) {
                $availableBalance = $carryBal + $firstBal;
            } else {
                $availableBalance = $carryBal + $secondBal + $firstBal;
            }

            // Determine paid vs unpaid split
            if ($availableBalance >= $daysToDeduct) {
                $paidAmount   = $daysToDeduct;
                $unpaidAmount = 0.0;
            } elseif ($availableBalance > 0) {
                $paidAmount   = $availableBalance;
                $unpaidAmount = $daysToDeduct - $availableBalance;
            } else {
                $paidAmount   = 0.0;
                $unpaidAmount = $daysToDeduct;
            }

            // Sequential deduction priority: carry_leaves -> second_annual -> first_annual
            if ($paidAmount > 0) {
                $toDeduct = $paidAmount;

                // 1. Deduct from carry_leaves first
                if ($record->carry_leaves > 0) {
                    $deductFromCarry = min($record->carry_leaves, $toDeduct);
                    $record->carry_leaves -= $deductFromCarry;
                    $toDeduct -= $deductFromCarry;
                }

                // 2. Deduct remaining required amount from annual leaves
                if ($toDeduct > 0) {
                    if ($isFirstHalf) {
                        $record->first_annual = max(0, (float) $record->first_annual - $toDeduct);
                    } else {
                        if ($record->second_annual >= $toDeduct) {
                            $record->second_annual -= $toDeduct;
                        } else {
                            $remainderToDeduct = $toDeduct - $record->second_annual;
                            $record->second_annual = 0;
                            $record->first_annual = max(0, (float) $record->first_annual - $remainderToDeduct);
                        }
                    }
                }

                $record->total_used += $paidAmount;

                $paidDuration = ($paidAmount >= 1.0) ? '1' : '2';
                $record->leaves()->create([
                    'rec_id'     => $record->id,
                    'leave_date' => $currentLeaveDate->toDateString(),
                    'duration'   => $paidDuration,
                    'day_count'  => $paidAmount,
                    'reason'     => $request->reason ?? 'Short leave accumulated',
                    'leave_type' => 1, // Paid
                ]);
            }

            if ($unpaidAmount > 0) {
                $unpaidDuration = ($unpaidAmount >= 1.0) ? '1' : '2';
                $record->leaves()->create([
                    'rec_id'     => $record->id,
                    'leave_date' => $currentLeaveDate->toDateString(),
                    'duration'   => $unpaidDuration,
                    'day_count'  => $unpaidAmount,
                    'reason'     => $request->reason ?? 'Short leave accumulated (Unpaid)',
                    'leave_type' => 0, // Unpaid
                ]);
            }

            // Calculate overall remaining leaves including carry_leaves
            $totalRemaining = (float) $record->carry_leaves + (float) $record->first_annual + (float) $record->second_annual;
            $record->remain_leaves = max(0, $totalRemaining);

            $hoursToDurationMap = [
                '3.5' => '3',
                '3.0' => '4', '3' => '4',
                '2.5' => '5',
                '2.0' => '6', '2' => '6',
                '1.5' => '7',
                '1.0' => '8', '1' => '8',
                '0.5' => '9',
            ];

            if ($totalRemaining <= 0) {
                $record->carry_leaves = 0;
                $record->remain_leaves = 0;
                $record->first_annual = 0;
                $record->second_annual = 0;
                $record->accumulated_hours = 0.0;

                $convertShortLeavesToUnpaid();

                if ($remainingHours > 0) {
                    $durationKey = (string) $remainingHours;
                    $remDurationId = $hoursToDurationMap[$durationKey] ?? '9';
                    $remainderDayCount = $remainingHours / 8.0;

                    $record->leaves()->create([
                        'rec_id'     => $record->id,
                        'leave_date' => $currentLeaveDate->toDateString(),
                        'duration'   => $remDurationId,
                        'day_count'  => $remainderDayCount,
                        'reason'     => ($request->reason ? $request->reason . ' ' : '') . '(Unpaid short leave - No leave balance)',
                        'leave_type' => 0, // Unpaid
                    ]);
                }
            } else {
                if ($remainingHours > 0) {
                    $durationKey = (string) $remainingHours;
                    $remDurationId = $hoursToDurationMap[$durationKey] ?? '9';

                    $record->accumulated_hours = $remainingHours;

                    $record->leaves()->create([
                        'rec_id'     => $record->id,
                        'leave_date' => $currentLeaveDate->toDateString(),
                        'duration'   => $remDurationId,
                        'day_count'  => 0,
                        'reason'     => $request->reason ?? 'Short leave remainder',
                        'leave_type' => 3, // Short Leave
                    ]);
                } else {
                    $record->accumulated_hours = 0.0;
                }
            }

        } else {
            // Under 4 accumulated hours check
            $totalRemaining = (float) $record->carry_leaves + (float) $record->first_annual + (float) $record->second_annual;
            $record->remain_leaves = max(0, $totalRemaining);

            if ($totalRemaining > 0) {
                $record->accumulated_hours = $totalHours;

                $record->leaves()->create([
                    'rec_id'     => $record->id,
                    'leave_date' => $currentLeaveDate->toDateString(),
                    'duration'   => (string) ($request->duration ?? 9),
                    'day_count'  => 0,
                    'reason'     => $request->reason ?? null,
                    'leave_type' => 3, // Short leave
                ]);
            } else {
                $record->accumulated_hours = 0.0;
                $convertShortLeavesToUnpaid();

                $unpaidDayCount = $addedHours / 8.0;

                $record->leaves()->create([
                    'rec_id'     => $record->id,
                    'leave_date' => $currentLeaveDate->toDateString(),
                    'duration'   => (string) ($request->duration ?? 9),
                    'day_count'  => $unpaidDayCount,
                    'reason'     => ($request->reason ? $request->reason . ' ' : '') . '(Unpaid short leave - No leave balance)',
                    'leave_type' => 0, // Unpaid Leave
                ]);
            }
        }

        $record->save();

        DB::commit();

        return response()->json([
            'message' => 'Short leave processed successfully.',
            'data'    => new LeaveRecordResource($record->load('leaves'))
        ], 200);

    } catch (\Throwable $e) {
        DB::rollBack();

        Utility::log("LeaveRecordController::addShortLeave", $e->getMessage() . " | Line: " . $e->getLine() . " | File: " . $e->getFile());
        Log::error("addShortLeave Exception: " . $e->getMessage(), [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'error'   => 'Failed to process short leave',
            'details' => $e->getMessage(),
        ], 500);
    }
}
}