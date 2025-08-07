<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
        return  [
            'id'                => $this->id,
            'staff_no'          => $this->staff_no,
            'staff_project'     => $this->staffProjects,
            'skill_sheet'       => $this->when(isset($data['skill_sheet']), $this->skillSheet),
            'eng_name'          => $this->eng_name,
            'jp_name'           => $this->jp_name,
            'username'          => $this->username,
            'address'           => $this->address,
            'ph_number'         => $this->ph_number,
            'position'          => $this->position,
            'role'              => $this->role,
            'email'             => $this->email,
            'permanent_date'    => $this->permanent_date,
            'ref_person'        => $this->ref_person,
            'ref_ph_number'     => $this->ref_ph_number,
            'sort_key'          => $this->sort_key,
            'task_performance'  => $this->when(
                isset($data['task_performance']),
                TaskPerformanceResource::collection($this->taskPerformance),
            ),
            'staff_image_url'   => $this->staff_image_url,
            'task_performance_setting'  => $this->when(
                isset($data['task_performance_setting']),
                TaskPerformanceSettingResource::collection($this->taskPerformanceSetting),
            ),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'deleted_at'        => $this->deleted_at,
        ];
    }
}
