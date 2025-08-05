<?php

namespace App\Http\Requests\Reporting;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class TaskPerformanceDeleteRequest extends FormRequest
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
            "delete_array"                  => ["required", "array"],
            "delete_array.*.date"           => ["required", "date", "date_format:Y-m-d"],
            "delete_array.*.staff_id"       => ["required", "integer", "exists:staffs,id"],
            "delete_array.*.period"         => ["required", "date_format:H:i"]
        ];
    }
}
