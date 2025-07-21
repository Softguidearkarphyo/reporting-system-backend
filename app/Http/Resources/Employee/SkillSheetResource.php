<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillSheetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id'                => $this->id,
            'staff'             => $this->staff,
            'project'           => $this->staffProejct->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->project->name,
                ];
            }),
            'position'          => $this->position,
            'grade'             => $this->grade,
            'join_date'         => $this->join_date,
            'sg_experience'     => $this->sg_experience,
            'prev_experience'   => $this->prev_experience,
            'total_experience'  => $this->total_experience,
            'responsibility'    => $this->staffProejct->map(function ($item) {
                return [
                    'id'              => $item->id,
                    'tech_stack_name' => $item->project->name,
                ];
            }),
            'japanese_level'    => $this->japaneseLevel,
            'major_tech_stack'  => $this->techStack,
            'tech_stack_proficiencies' => $this->techStackProficiencies->map(function ($item) {
                return [
                    'tech_stack_id'   => $item->tech_stack_id,
                    'tech_stack_name' => $item->techStack->name,
                    'proficiency_level_id' => $item->proficiency_level_id,
                    'symbol' => $item->proficiencyLevel->abbv,
                ];
            }),
        ];
    }
}
