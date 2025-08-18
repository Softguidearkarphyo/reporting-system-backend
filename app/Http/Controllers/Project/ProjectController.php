<?php

namespace App\Http\Controllers\Project;

use App\Utility;
use Carbon\Carbon;
use App\ReturnMessage;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\TaskPerformance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Project\ProjectResource;
use App\Http\Requests\Project\ProjectGetRequest;
use App\Http\Requests\Project\ProjectCreateRequest;
use App\Http\Requests\Project\ProjectDeleteRequest;
use App\Http\Requests\Project\ProjectUpdateRequest;
use App\Http\Resources\Reporting\ProjectWorkHourResource;

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
                "cd"        => $data['cd'],
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
                "cd"        => $data['cd'],
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

    public function getProjectHour(Request $request)
    {
        try {
            $data = $request->all();
            $end_date = Carbon::parse($data['end_date']);

            if (isset($data['week_date'])) {
                $week_count = (int)$data['week_date'];
                $startDate = $end_date->copy()->subWeeks($week_count);
                $query = TaskPerformance::whereHas('staff')->whereHas('project');
                $result = $query->whereBetween('date', [$startDate, $end_date])->get();
                $projects_hour = ProjectWorkHourResource::collection($result);
                return response()->json($projects_hour);
            } else {
                $start_date = Carbon::parse($data['start_date'])->startOfMonth();
                $end_date = $end_date->endOfMonth();
                $query = TaskPerformance::whereHas('staff')->whereHas('project');
                $result = $query->whereBetween('date', [$start_date, $end_date])->get();
                $projects_hour = ProjectWorkHourResource::collection($result);
                return response()->json($projects_hour);
            }
        } catch (\Throwable  $e) {
            Utility::log("ProjectController::get", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
}
