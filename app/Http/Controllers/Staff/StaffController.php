<?php

namespace App\Http\Controllers\Staff;

use DateTime;
use App\Utility;
use Carbon\Carbon;
use App\Models\Staff;
use App\ReturnMessage;
use App\Models\StaffFine;
use App\Models\StaffProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Staff\StaffResource;
use App\Http\Requests\Staff\StaffGetRequest;
use App\Http\Requests\Staff\StaffFineRequest;
use App\Http\Requests\Staff\StaffCreateRequest;
use App\Http\Requests\Staff\StaffDeleteRequest;
use App\Http\Requests\Staff\StaffUpdateRequest;
use App\Http\Resources\Staff\StaffFineResource;
use App\Http\Requests\Staff\StaffFineDeleteRequest;

class StaffController extends Controller
{
    public function get(StaffGetRequest $request)
    {
        try {
            $data   = $request->all();
            $query  = Staff::query();
            if (!empty($data['id'])) {
                $query->where('id', $data['id']);
            }
            $staffs = $query->get();
            $staffs = StaffResource::collection($staffs);
            return response()->json($staffs);
        } catch (\Throwable  $e) {
            Utility::log("MemberController::get", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function create(StaffCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $createData = [
                "staff_no"          => $data['staff_no'],
                "eng_name"          => $data['eng_name'],
                "jp_name"           => $data['jp_name'],
                "username"          => $data['username'],
                "password"          => Hash::make($data['password']),
                "address"           => $data['address'],
                "ph_number"         => $data['ph_number'],
                "position"          => $data['position'],
                "role"              => $data['role'],
                "email"             => $data['email'],
                "permanent_date"    => $data['permanent_date'],
                "ref_person"        => $data['ref_person'] ?? null,
                "ref_ph_number"     => $data['ref_ph_number'] ?? null,
                "sort_key"          => $data['sort_key'] ?? null,
            ];
            $staff = new StaffResource(Staff::create($createData));
            if (!empty($data['project']) && is_array($data['project'])) {
                $insertData = [];
                foreach ($data['project'] as $projectId) {
                    $insertData[] = [
                        'staff_id'   => $staff->id,
                        'project_id' => $projectId,
                    ];
                }
                StaffProject::insert($insertData);
            }
            DB::commit();
            return ["status" => ReturnMessage::OK];
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("MemberController::create", $e->getMessage());
            return ["status" => ReturnMessage::INTERNAL_SERVER_ERROR];
        }
    }

    public function update(StaffUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data   = $request->all();
            $updateData = [
                "staff_no"          => $data['staff_no'],
                "eng_name"          => $data['eng_name'],
                "jp_name"           => $data['jp_name'],
                "username"          => $data['username'],
                "address"           => $data['address'],
                "ph_number"         => $data['ph_number'],
                "position"          => $data['position'],
                "role"              => $data['role'],
                "email"             => $data['email'],
                "permanent_date"    => $data['permanent_date'],
                "ref_person"        => $data['ref_person'] ?? null,
                "ref_ph_number"     => $data['ref_ph_number'] ?? null,
                "sort_key"          => $data['sort_key'] ?? null,
            ];
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }
            $Staff = Staff::where('id', $data['id'])->first();
            $Staff->update($updateData);

            StaffProject::where('staff_id', $data['id'])->delete();

            if (!empty($data['project']) && is_array($data['project'])) {
                $insertData = [];
                foreach ($data['project'] as $projectId) {
                    $insertData[] = [
                        'staff_id'   => $data['id'],
                        'project_id' => $projectId,
                    ];
                }
                StaffProject::insert($insertData);
            }
            DB::commit();
            return ["status" => ReturnMessage::OK, 'staff' => $Staff];
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("MemberController::update", $e->getMessage());
            return ["status" => ReturnMessage::INTERNAL_SERVER_ERROR];
        }
    }

    public function delete(StaffDeleteRequest $request)
    {
        DB::beginTransaction();
        try {
            $data   = $request->all();
            Staff::where('id', $data['id'])->update(['deleted_at' => now()]);
            DB::commit();
            return response()->json($data['id']);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("MemberController::delete", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function createFines(StaffFineRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            // $time = DateTime::createFromFormat('H:i:s', $data['time']);
            // $eightAM = DateTime::createFromFormat('H:i:s', '08:30:00');
            // $nineAM = DateTime::createFromFormat('H:i:s', '09:00:00');
            // $tenAm = DateTime::createFromFormat('H:i:s', '10:00:00');
            // if ($time >  $eightAM && $time <= $nineAM) {
            //     $lateFine = 2000;
            // } else if ($time > $nineAM && $data['time'] <= $tenAm) {
            //     $lateFine = 5000;
            // } else if ($time > $tenAm) {
            //     $lateFine = 10000;
            // }
            if ($data['time'] == 1) {
                $lateFine = 2000;
                $time = DateTime::createFromFormat('H:i:s', '08:31:00');
            } elseif ($data['time'] == 2) {
                $lateFine = 5000;
                $time = DateTime::createFromFormat('H:i:s', '09:01:00');
            } else {
                $lateFine = 10000;
                $time = DateTime::createFromFormat('H:i:s', '10:31:00');
            }
            $createData = [
                'staff_id' => $data['staff'],
                'date'     => $data['date'],
                'time'     => $time,
                'amount'   => $lateFine,
                'status'   => 0,
            ];
            StaffFine::create($createData);
            DB::commit();
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("MemberController::createFines", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function getFines()
    {
        try {
            $query = StaffFine::query();

            $staffFines = $query->whereHas('staff')->get();
            $staffFines = StaffFineResource::collection($staffFines);
            return response()->json(["data" => $staffFines]);
        } catch (\Throwable  $e) {
            Utility::log("MemberController::getFines", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteFines(StaffFineDeleteRequest $request)
    {
        DB::beginTransaction();
        try {
            $data   = $request->all();
            StaffFine::where('id', $data['id'])->update(['deleted_at' => now()]);
            DB::commit();
            return response()->json($data['id']);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("MemberFineController::delete", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function statusChange(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            if ($data['status'] == 0) {
                StaffFine::where('id', $data['id'])->update(['status' => 1]);
            } else {
                StaffFine::where('id', $data['id'])->update(['status' => 0]);
            }
            DB::commit();
            return response()->json($data['id']);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("MemberFineController::delete", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
}
