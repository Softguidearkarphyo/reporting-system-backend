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
                'staff_id'       => $data['staff_id'],
                'year'           => $targetYear,
                'permanent_date' => $data['permanent_date'],
                'remain_leaves'  => $totalLeave,
                'total_leaves'   => $totalLeave,
                'first_annual'   => $firstHalfLeave,
                'second_annual'  => $secondHalfLeave,
                'total_used'     => 0,
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
}