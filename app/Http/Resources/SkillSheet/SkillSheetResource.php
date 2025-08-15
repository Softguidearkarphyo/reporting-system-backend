<?php

namespace App\Http\Resources\SkillSheet;

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
        $data = $request->all();
        return  [
            'id'                => $this->id,
            'staff'             => $this->when(
                isset($data['staff']),
                $this->staff,
            ),
            'staff_project' => $this->when(
                isset($data['staff_project']),
                $this->staffProejct->map(function ($item) {
                    return [
                        'id'      => $item->id,
                        'project' => $item->project
                    ];
                })
            ),
            'position'          => $this->position,
            'grade'             => $this->grade,
            'join_date'         => $this->join_date,
            'sg_experience'     => $this->sg_experience,
            'prev_experience'   => $this->prev_experience,
            'total_experience'  => $this->total_experience,
            'staff_responsibility' => $this->when(
                isset($data['staff_responsibility']),
                function () {
                    return $this->staffResponsibility->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'responsibility' => $item->responsibility
                        ];
                    });
                }
            ),

            'japanese_level'    => $this->japaneseLevel,
            'major_tech_stack'  => $this->techStack,
            'tech_stack_proficiencies' => $this->when(
                isset($data['tech_stack_proficiencies']),
                function () {
                    return $this->techStackProficiencies->map(function ($item) {
                        return [
                            'tech_stack_id'         => $item->tech_stack_id,
                            'tech_stack_name'       => $item->techStack->name,
                            'proficiency_level_id'  => $item->proficiency_level_id,
                            'symbol'                => $item->proficiencyLevel->abbv,
                        ];
                    });
                }
            ),
            'updated_at'  => $this->updated_at,
        ];
    }
}
