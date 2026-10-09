<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Staff;
use App\ReturnMessage;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function getLocationStaffs(Request $request)
    {
        try {
            $staffs = Staff::whereNull('deleted_at')
            ->select('id', 'staff_no', 'eng_name', 'jp_name', 'email','work_type')
            ->get();

            $staffIds = $staffs->pluck('id');
            $locations = Location::whereIn('staff_id', $staffIds)->get()->keyBy('staff_id');

            $result = $staffs->map(function ($staff) use ($locations) {
                $location = $locations->get($staff->id);
                return [
                    'id'          => $staff->id,
                    'staff_no'    => $staff->staff_no,
                    'eng_name'    => $staff->eng_name,
                    'work_type'    => $staff->work_type,
                    'jp_name'     => $staff->jp_name,
                    'lat'         => $location ? $location->lat : null,
                    'lon'         => $location ? $location->lon : null,
                    'allow_meter' => $location ? $location->allow_meter : null,
                    'device_uuid' => $location ? $location->device_uuid : null,
                ];
            });

            return response()->json($result);
        } catch (\Throwable $e) {
            Utility::log("LocationController::getLocationStaffs", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function saveLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id'    => 'required|exists:staffs,id',
            'lat'         => 'nullable|numeric',
            'lon'         => 'nullable|numeric',
            'allow_meter' => 'nullable|integer',
            'device_uuid' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'validation_error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        try {
            DB::transaction(function () use ($validated) {
                $location = Location::withTrashed()->where('staff_id', $validated['staff_id'])->lockForUpdate()->first();

                if ($location) {
                    if ($location->trashed()) {
                        $location->restore();
                    }
                    $location->update([
                        'lat'         => $validated['lat'] ?? null,
                        'lon'         => $validated['lon'] ?? null,
                        'allow_meter' => $validated['allow_meter'] ?? null,
                        'device_uuid' => $validated['device_uuid'] ?? null,
                    ]);
                } else {
                    Location::create([
                        'staff_id'    => $validated['staff_id'],
                        'lat'         => $validated['lat'] ?? null,
                        'lon'         => $validated['lon'] ?? null,
                        'allow_meter' => $validated['allow_meter'] ?? null,
                        'device_uuid' => $validated['device_uuid'] ?? null,
                    ]);
                }
            });

            return response()->json([
                'status'  => ReturnMessage::OK,
                'message' => 'Location saved successfully.',
            ], 200);

        } catch (\Throwable $e) {
            Utility::log("LocationController::saveLocation", $e->getMessage());
            return response()->json([
                'status'  => ReturnMessage::INTERNAL_SERVER_ERROR,
                'message' => 'Server Error',
            ], 500);
        }
    }

    public function deleteLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:locations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $locationId = $request->input('id');

        try {
            DB::transaction(function () use ($locationId) {
                $location = Location::findOrFail($locationId);

                $location->update([
                    'lat'         => 0,
                    'lon'         => 0,
                    'allow_meter' => 0,
                    'device_uuid' => null,
                ]);

                $location->delete();
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Location deleted and reset successfully.',
                'id'      => $locationId,
            ], 200);

        } catch (\Throwable $e) {
            Utility::log("LocationController::delete", $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Server Error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}