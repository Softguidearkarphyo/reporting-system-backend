<?php

namespace App\Http\Controllers\SkillSheet;

use App\Utility;
use App\ReturnMessage;
use App\Models\SkillSheet;
use App\Models\StaffProject;
use Illuminate\Support\Facades\DB;
use App\Models\StaffResponsibility;
use App\Http\Controllers\Controller;
use App\Models\TechStackProficiency;
use App\Http\Resources\SkillSheet\SkillSheetResource;
use App\Http\Requests\SkillSheet\SkillSheetGetRequest;
use App\Http\Requests\SkillSheet\SkillSheetCreateRequest;
use App\Http\Requests\SkillSheet\SkillSheetUpdateRequest;

class SkillSheetController extends Controller
{
    public function get(SkillSheetGetRequest $request)
    {
        try {
            $data   = $request->all();
            $query  = SkillSheet::query();
            if (!empty($data['id'])) {
                $query->where('id', $data['id']);
            }
            $data = $query->get();
            $skillSheet =  SkillSheetResource::collection($data);
            return response()->json($skillSheet);
        } catch (\Throwable  $e) {
            Utility::log("SkillSheetController::getSkillSheet", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function create(SkillSheetCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            StaffProject::where('staff_id', $data['staff_id'])->delete();
            if (!empty($data['project']) && is_array($data['project'])) {
                $insertData = [];
                foreach ($data['project'] as $projectId) {
                    $insertData[] = [
                        'staff_id'   => $data['staff_id'],
                        'project_id' => $projectId,
                    ];
                }
                StaffProject::insert($insertData);
            }
            StaffResponsibility::where('staff_id', $data['staff_id'])->delete();
            if (!empty($data['responsibility']) && is_array($data['responsibility'])) {
                $insertData = [];
                foreach ($data['responsibility'] as $responsibilityId) {
                    $insertData[] = [
                        'staff_id'          => $data['staff_id'],
                        'responsibility_id' => $responsibilityId,
                    ];
                }
                StaffResponsibility::insert($insertData);
            }
            if (!empty($data['skills']) && is_array($data['skills'])) {
                $insertData = [];
                foreach ($data['skills'] as $skill) {
                    if (
                        isset($skill['tech_stack_id'], $skill['proficiency_level_id']) &&
                        $skill['tech_stack_id'] !== null &&
                        $skill['proficiency_level_id'] !== null
                    ) {
                        $insertData[] = [
                            'staff_id'             => $data['staff_id'],
                            'tech_stack_id'        => $skill['tech_stack_id'],
                            'proficiency_level_id' => $skill['proficiency_level_id'],
                        ];
                    }
                }
                TechStackProficiency::insert($insertData);
            }

            $insertData = [
                "staff_id"             => $data['staff_id'],
                "position_id"          => $data['position'],
                "grade_id"             => $data['grade'],
                "join_date"            => $data['join_date'],
                "sg_experience"        => $data['sg_experience'],
                "prev_experience"      => $data['prev_experience'],
                "total_experience"     => $data['total_experience'],
                "japanese_level_id"    => $data['japanese_level'],
                "major_tech_stack_id"  => $data['major_tech_stack_id'],
            ];
            SkillSheet::create($insertData);
            DB::commit();
            return ["status" => ReturnMessage::OK];
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("SkillSheetController::createSkillSheet", $e->getMessage());
            return ["status" => ReturnMessage::INTERNAL_SERVER_ERROR];
        }
    }

    public function update(SkillSheetUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            StaffProject::where('staff_id', $data['staff_id'])->delete();
            if (!empty($data['project']) && is_array($data['project'])) {
                $insertData = [];
                foreach ($data['project'] as $projectId) {
                    $insertData[] = [
                        'staff_id'   => $data['staff_id'],
                        'project_id' => $projectId,
                    ];
                }
                StaffProject::insert($insertData);
            }
            StaffResponsibility::where('staff_id', $data['staff_id'])->delete();
            if (!empty($data['responsibility']) && is_array($data['responsibility'])) {
                $insertData = [];
                foreach ($data['responsibility'] as $responsibilityId) {
                    $insertData[] = [
                        'staff_id'          => $data['staff_id'],
                        'responsibility_id' => $responsibilityId,
                    ];
                }
                StaffResponsibility::insert($insertData);
            }
            TechStackProficiency::where('staff_id', $data['staff_id'])->delete();
            if (!empty($data['skills']) && is_array($data['skills'])) {
                $insertData = [];
                foreach ($data['skills'] as $skill) {
                    if (
                        isset($skill['tech_stack_id'], $skill['proficiency_level_id']) &&
                        $skill['tech_stack_id'] !== null &&
                        $skill['proficiency_level_id'] !== null
                    ) {
                        $insertData[] = [
                            'staff_id'             => $data['staff_id'],
                            'tech_stack_id'        => $skill['tech_stack_id'],
                            'proficiency_level_id' => $skill['proficiency_level_id'],
                        ];
                    }
                }
                TechStackProficiency::insert($insertData);
            }

            $insertData = [
                "staff_id"             => $data['staff_id'],
                "position_id"          => $data['position'],
                "grade_id"             => $data['grade'],
                "join_date"            => $data['join_date'],
                "sg_experience"        => $data['sg_experience'],
                "prev_experience"      => $data['prev_experience'],
                "total_experience"     => $data['total_experience'],
                "japanese_level_id"    => $data['japanese_level'],
                "major_tech_stack_id"  => $data['major_tech_stack_id'],
            ];
            $skillSheet = SkillSheet::where('id', $data['id'])->first();
            $skillSheet->update($insertData);
            DB::commit();
            return ["status" => ReturnMessage::OK];
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("SkillSheetController::createSkillSheet", $e->getMessage());
            return ["status" => ReturnMessage::INTERNAL_SERVER_ERROR];
        }
    }
}
