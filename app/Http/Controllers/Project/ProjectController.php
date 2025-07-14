<?php

namespace App\Http\Controllers\Project;

use App\Utility;
use App\ReturnMessage;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Project\ProjectResource;
use App\Http\Requests\Project\ProjectGetRequest;
use App\Http\Requests\Project\ProjectCreateRequest;
use App\Http\Requests\Project\ProjectDeleteRequest;
use App\Http\Requests\Project\ProjectUpdateRequest;

class ProjectController extends Controller
{
    public function get(ProjectGetRequest $request)
    {
        try {
            $data   = $request->all();
            $query  = Project::query();
            if (!empty($data['id'])) {
                $query->where('id', $data['id']);
            }
            $projects = $query->get();
            $projects = ProjectResource::collection($projects);
            return response()->json($projects);
        } catch (\Throwable  $e) {
            Utility::log("ProjectController::get", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }

    public function create(ProjectCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $createData = [
                "code"      => $data['code'],
                "eng_name"  => $data['eng_name'],
                "jp_name"   => $data['jp_name'],
            ];
            $project = new ProjectResource(Project::create($createData));
            DB::commit();
            return response()->json($project);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("ProjectController::create", $e->getMessage());
            return ["status" => ReturnMessage::INTERNAL_SERVER_ERROR];
        }
    }


    public function update(ProjectUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data   = $request->all();
            $updateData = [
                "code"      => $data['code'],
                "eng_name"  => $data['eng_name'],
                "jp_name"   => $data['jp_name'],
            ];
            $project = Project::where('id', $data['id'])->first();
            $project->update($updateData);
            DB::commit();
            return response()->json(new ProjectResource($project));
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("ProjectController::update", $e->getMessage());
            return ["status" => ReturnMessage::INTERNAL_SERVER_ERROR];
        }
    }

    public function delete(ProjectDeleteRequest $request)
    {
        DB::beginTransaction();
        try {
            $data   = $request->all();
            Project::where('id', $data['id'])->update(['deleted_at' => now()]);
            DB::commit();
            return response()->json($data['id']);
        } catch (\Throwable  $e) {
            DB::rollBack();
            Utility::log("ProjectController::delete", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
}
