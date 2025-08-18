<?php

namespace App\Http\Resources\Staff;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Location\LocationResource;
use App\Http\Resources\SkillSheet\SkillSheetResource;
use App\Http\Resources\Reporting\TaskPerformanceResource;
use App\Http\Resources\Reporting\TaskPerformanceSettingResource;

class StaffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $request->all();
        $leave = $this->when(isset($data['leave']), function () {
            return $this->leave_records->filter(function ($item) {
                return Carbon::parse($item->leave_date)->format('Y-m') === Carbon::now()->format('Y-m');
            })->values()->all();
        });
        $over_time = $this->when(isset($data['over_time']), function () {
            return $this->over_times->filter(function ($item) {
                return Carbon::parse($item->ot_date)->format('Y-m') === Carbon::now()->format('Y-m');
            })->values()->all();
        });

        return  [
            'id'                => $this->id,
            'staff_no'          => $this->staff_no,
            'staff_project'     => $this->when(
                isset($data['staff_project']),
                $this->staffProjects,
            ),
            'skill_sheet' => $this->when(
                isset($data['skill_sheet']),
                new SkillSheetResource($this->skillSheet),
            ),
            'eng_name'          => $this->eng_name,
            'jp_name'           => $this->jp_name,
            'username'          => $this->username,
            'address'           => $this->address,
            'ph_number'         => $this->ph_number,
            'position'          => $this->position,
            'role'              => $this->role,
            'email'             => $this->email,
            'leave'             => $leave,
            'fine'              => $this->when(isset($data['fine']), $this->staffFine),
            'over_times'        => $over_time,
            'permanent_date'    => $this->permanent_date,
            'ref_person'        => $this->ref_person,
            'ref_ph_number'     => $this->ref_ph_number,
            'sort_key'          => $this->sort_key,
            'task_performance'  => $this->when(
                isset($data['task_performance']),
                TaskPerformanceResource::collection($this->taskPerformance),
            ),
            'staff_image_url'   => isset($this->staff_image) ? asset('images/staffs/' . $this->staff_image) : null,
            'task_performance_setting'  => $this->when(
                isset($data['task_performance_setting']),
                TaskPerformanceSettingResource::collection($this->taskPerformanceSetting),
            ),
            'location'          => $this->when(
                isset($data['location']),
                new LocationResource($this->location),
            ),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'deleted_at'        => $this->deleted_at,
        ];
    }
}
