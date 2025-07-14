<?php

namespace App\Http\Requests\Project;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProjectUpdateRequest extends FormRequest
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
            "id"        => ["required", "integer", "exists:projects,id"],
            "code"      => ["required", "string", "max:30", Rule::unique('projects', 'code')->ignore($this->id)],
            "eng_name"  => ["required", "string", "max:50"],
            "jp_name"   => ["required", "string", "max:50"],
        ];
    }
}
