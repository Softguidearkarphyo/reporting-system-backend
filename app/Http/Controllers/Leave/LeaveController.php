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
use Carbon\Carbon;

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
            if (!isset($data['staff_id']) || !isset($data['leave_type'])) {
                throw new \Exception("Required fields are missing");
            }

            $staffExists = DB::table('staffs')->where('id', $data['staff_id'])->exists();
            if (!$staffExists) {
                throw new \Exception("Staff with ID {$data['staff_id']} does not exist");
            }

            $leaves = [];
            if (isset($data['start_date'])) {
                $startDate = Carbon::parse($data['start_date']);
                $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date']) : $startDate;

                if ($startDate->gt($endDate)) {
                    throw new \Exception("Start date cannot be after end date");
                }

                for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                    $createData = [
                        "staff_id"    => $data['staff_id'],
                        "leave_type"  => $data['leave_type'],
                        "leave_date"  => $date->format('Y-m-d'),
                        "duration"    => 'full',
                        "reason"      => $data['reason'] ?? null,
                        "day_count"   => 1,
                    ];

                    $leave = Leave::create($createData);
                    $leaves[] = new LeaveResource($leave);
                }
            } else {
                if (!isset($data['leave_date'])) {
                    throw new \Exception("Leave date is required for single-day leave");
                }

                $createData = [
                    "staff_id"    => $data['staff_id'],
                    "leave_type"  => $data['leave_type'],
                    "leave_date"  => $data['leave_date'],
                    "duration"    => $data['duration'] ?? null,
                    "reason"      => $data['reason'] ?? null,
                    "day_count"   => 1,
                ];

                $leave = Leave::create($createData);
                $leaves[] = new LeaveResource($leave);
            }

            DB::commit();

            return response()->json([
                'message' => 'Leave(s) created successfully',
                'data' => $leaves
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
