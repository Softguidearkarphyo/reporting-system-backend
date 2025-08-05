<?php

namespace App\Http\Resources\Reporting;

use Illuminate\Http\Request;
use App\Http\Resources\Reporting\TaskResource;
use App\Http\Resources\Project\ProjectResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskPerformanceResource extends JsonResource
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
            'date'          => $this->date,
            'staff_id'      => $this->staff_id,
            'project_id'    => $this->project_id,
            'task_id'       => $this->task_id,
            'period'        => $this->period,
            'project'       => $this->when(
                isset($data['project']),
                new ProjectResource($this->project),
            ),
            'task'          => $this->when(
                isset($data['task']),
                new TaskResource($this->task),
            ),
        ];
    }
}
