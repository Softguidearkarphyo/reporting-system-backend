<?php

namespace App\Http\Resources\Reporting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectWorkHourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'staff_id'      => $this->staff->id,
            'eng_name'      => $this->staff->eng_name,
            'jp_name'       => $this->staff->jp_name,
            'project_eng'   => $this->project->eng_name,
            'project_jp'    => $this->project->jp_name,
            'periods'       => $this->period,
            'project_id'    => $this->project->id,
            'date'          => $this->date,
        ];
    }
}
