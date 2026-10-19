<?php

namespace App\Http\Controllers\OverTime;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Validator;
use App\Models\OverTime;
use App\Http\Resources\OverTime\OverTimeResource;
use App\Http\Requests\OverTime\OverTimeGetRequest;
use App\Http\Requests\OverTime\OverTimeCreateRequest;
use App\Http\Requests\OverTime\OverTimeDeleteRequest; 

class OverTimeController extends Controller
{
    public function get(OverTimeGetRequest $request)
    {
        try {
            $data = $request->all();
            $query = OverTime::with('staff');

            if (!empty($data['id'])) {
                $query->where('id', $data['id']);
            }

            if (!empty($data['staff_id'])) {
                $query->where('staff_id', $data['staff_id']);
            }

            $overtime = $query->get();
            $overtime = OverTimeResource::collection($overtime);

            return response()->json($overtime);
        } catch (\Throwable $e) {
            Log::error("OverTimeController::get error: " . $e->getMessage());
            return response()->json([
                'message' => 'Server Error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function create(OverTimeCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // If staff_id and ot_date already exist, update ot_time. Otherwise, create a new record.
            $otRecord = OverTime::updateOrCreate(
                [
                    "staff_id" => $data['staff_id'],
                    'ot_date'  => $data['ot_date'],
                ],
                [
                    'ot_time'  => $data['ot_time'],
                ]
            );

            $ot = new OverTimeResource($otRecord);
            DB::commit();
            return response()->json($ot);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("OverTimeController::create error: " . $e->getMessage());
            return response()->json([
                'message' => 'Server Error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function statusChange(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id'     => 'required|exists:over_times,id',
                'status' => 'required|integer|in:0,1,2,3',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'validation_error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            OverTime::where('id', $data['id'])->update(['status' => $data['status']]);

            DB::commit();

            return response()->json([
                'status'         => 'success',
                'id'             => $data['id'],
                'updated_status' => $data['status']
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("OverTimeController::statusChange error: " . $e->getMessage());

            return response()->json([
                'message' => 'Server Error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:over_times,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'validation_error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            OverTime::where('id', $data['id'])->update(['deleted_at' => now()]);
            
            DB::commit();
            return response()->json($data['id']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("OverTimeController::delete error: " . $e->getMessage());
            return response()->json([
                'message' => 'Server Error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}