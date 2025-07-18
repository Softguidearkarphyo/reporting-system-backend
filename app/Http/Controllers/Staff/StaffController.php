<?php

namespace App\Http\Controllers\Staff;

use App\Utility;
use App\Models\Staff;
use App\Models\StaffProject;
use App\ReturnMessage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Staff\StaffResource;
use App\Http\Requests\Staff\StaffGetRequest;
use App\Http\Requests\Staff\StaffCreateRequest;
use App\Http\Requests\Staff\StaffUpdateRequest;
use App\Http\Requests\Staff\StaffDeleteRequest;

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
                "sort_key"          => $data['sort_key']
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
                "ref_person"        => $data['ref_person'],
                "ref_ph_number"     => $data['ref_ph_number'],
                "sort_key"          => $data['sort_key']
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
            return ["status" => ReturnMessage::OK];
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
}
