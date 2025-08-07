<?php

namespace App\Http\Requests\SkillSheet;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SkillSheetCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'staff_id'                       => ['required', 'integer', Rule::unique('skill_sheets', 'staff_id')],
            'project'                        => ['required', 'array'],
            'project.*'                      => ['integer'],
            'position'                       => ['required', 'integer'],
            'grade'                          => ['required', 'integer'],
            'join_date'                      => ['required', 'date'],
            'sg_experience'                  => ['nullable', 'integer'],
            'prev_experience'                => ['nullable', 'integer'],
            'total_experience'               => ['nullable', 'integer'],
            'japanese_level'                 => ['required', 'integer'],
            'responsibility'                 => ['required', 'array'],
            'responsibility.*'               => ['integer'],
            'major_tech_stack_id'            => ['required', 'integer'],
            'skills'                         => ['required', 'array'],
            'skills.proficiency_level_id.*'  => ['nullable', 'integer'],
            'skills.tech_stack_id.*'         => ['nullable', 'integer'],
        ];
    }
}
