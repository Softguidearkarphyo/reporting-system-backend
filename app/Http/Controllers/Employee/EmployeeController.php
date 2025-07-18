<?php

namespace App\Http\Controllers\Employee;

use App\ReturnMessage;
use App\Utility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\EmployeeAddSkillRequest;
use App\Http\Resources\Employee\AddEmployeeSkillResource;
use App\Models\SkillSheet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;



class EmployeeController extends Controller
{
    public function get(EmployeeAddSkillRequest $request)
    {
        try {
            $data   = $request->all();
            $query  = SkillSheet::query();
            if (!empty($data['id'])) {
                $query->where('id', $data['id']);
            }
            $data = $query->get();
            return AddEmployeeSkillResource::collection($data);
        } catch (\Throwable  $e) {
            Utility::log("ProjectController::get", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }


    public function create(EmployeeAddSkillRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $columns = Schema::getColumnListing('skills');
            $nonSkillCols = ['id', 'created_at', 'updated_at'];
            $skillColumns = array_diff($columns, $nonSkillCols);

            function normalizeSkillName($name)
            {
                $exceptions = ['AWS S3'];
                if (in_array($name, $exceptions)) {
                    return $name;
                }
                return str_replace(['.', ' '], '', $name);
            }

            $skillInsertData = array_fill_keys($skillColumns, null);
            foreach ($data['skills'] as $skill) {
                $col = normalizeSkillName($skill['name']);
                if (in_array($col, $skillColumns)) {
                    $skillInsertData[$col] = $skill['symbol'];
                }
            }
            // $skill = Skill::create($skillInsertData);
            $insertData = [
                "staff_id"         => $data['name'],
                "position"         => $data['position'],
                "grade"            => $data['grade'],
                "join_date"        => $data['join_date'],
                "sg_experience"    => $data['sg_experience'],
                "prev_experience"  => $data['prev_experience'],
                "total_experience" => $data['total_experience'],
                "japanese_level"   => $data['japanese_level'],
                "expertise"        => $data['expertise'],
                "skill_id"         => $skill->id
            ];
            // dd($insertData);
            // SkillSet::create($insertData);
            DB::commit();
            return ["status" => ReturnMessage::OK];
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("EmployeeController::create", $e->getMessage());
            return ["status" => ReturnMessage::INTERNAL_SERVER_ERROR];
        }
    }
}
