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
use App\Http\Requests\Location\LocationDeleteRequest;
class LocationController extends Controller
{
    
    public function getRemoteStaffs(Request $request)
    {
        try {
            $staffs = Staff::where('work_type', 2)
                ->whereNull('deleted_at')
                ->select('id', 'staff_no', 'eng_name', 'jp_name', 'email')
                ->get();

            // Fetch locations indexed by staff_id
            $staffIds = $staffs->pluck('id');
            $locations = Location::whereIn('staff_id', $staffIds)->get()->keyBy('staff_id');

            $result = $staffs->map(function ($staff) use ($locations) {
                $location = $locations->get($staff->id);
                return [
                    'id'          => $staff->id,
                    'staff_no'    => $staff->staff_no,
                    'eng_name'    => $staff->eng_name,
                    'jp_name'     => $staff->jp_name,
                    'lat'         => $location ? $location->lat : null,
                    'lon'         => $location ? $location->lon : null,
                    'allow_meter' => $location ? $location->allow_meter : null,
                    'device_uuid' => $location ? $location->device_uuid : null,
                ];
            });

            return response()->json($result);
        } catch (\Throwable $e) {
            Utility::log("LocationController::getRemoteStaffs", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Save or update staff location settings.
     */
    public function saveLocation(Request $request)
    {
        DB::beginTransaction();
        try {
          $request->validate([
    'staff_id'    => 'required|exists:staffs,id',
    'lat'         => 'nullable|numeric',
    'lon'         => 'nullable|numeric',
    'allow_meter' => 'nullable|integer',
    'device_uuid' => 'nullable|string|max:255',
]);

    Location::withTrashed()->updateOrCreate(
        ['staff_id' => $request->staff_id],
        [
            'lat'         => $request->lat,
            'lon'         => $request->lon,
            'allow_meter' => $request->allow_meter,
            'device_uuid' => $request->device_uuid,
            'deleted_at'  => null, 
        ]
    );


            DB::commit();
            return response()->json(['status' => ReturnMessage::OK, 'message' => 'Location saved successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Utility::log("LocationController::saveLocation", $e->getMessage());
            return response()->json(['status' => ReturnMessage::INTERNAL_SERVER_ERROR], 500);
        }
    }

    //  public function deleteLocation(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'id' => 'required|exists:locations,id',
    //     ]);

    //     try {
    //         Location::where('id', $request->id)->delete();

    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Location record deleted successfully.',
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Failed to delete location record.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function deleteLocation(Request $request)
{
    DB::beginTransaction();
    try {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:locations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        Location::where('id', $data['id'])->update(['deleted_at' => now()]);
        
        DB::commit();
        return response()->json($data['id']);
    } catch (\Throwable $e) {
        DB::rollBack();
        Utility::log("LocationController::delete", $e->getMessage());
        return response()->json([
            'message' => 'Server Error',
            'error'   => $e->getMessage()
        ], 500);
    }
}

}