<?php

namespace App\Http\Requests\Reporting;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class TaskPerformanceCreateRequest extends FormRequest
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
            "create_array"                  => ["required", "array"],
            "create_array.*.date"           => ["required", "date", "date_format:Y-m-d"],
            "create_array.*.staff_id"       => ["required", "integer", "exists:staffs,id"],
            "create_array.*.project_id"     => ["required", "integer", "exists:projects,id"],
            "create_array.*.task_id"        => ["required", "integer", "exists:tasks,id"],
            "create_array.*.period"         => ["required", "date_format:H:i"]
        ];
    }
}
