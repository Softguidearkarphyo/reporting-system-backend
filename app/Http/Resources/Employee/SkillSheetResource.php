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
            'id'     => $this->id,
            'staff'  => $this->staff->id,
            'tech_stack_proficiencies' => $this->techStackProficiencies->map(function ($item) {
                return [
                    'tech_stack_id' => $item->tech_stack_id,
                    'proficiency_level_id' => $item->proficiency_level_id,
                ];
            }),
        ];
    }
}
