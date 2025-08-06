<?php

namespace App\Http\Resources\Reporting;

use Illuminate\Http\Request;
use App\Http\Resources\Reporting\TaskResource;
use App\Http\Resources\Project\ProjectResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskPerformanceSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'day'           => $this->day,
            'staff_id'      => $this->staff_id,
            'project_id'    => $this->project_id,
            'task_id'       => $this->task_id,
            'period'        => $this->period,
        ];
    }
}
