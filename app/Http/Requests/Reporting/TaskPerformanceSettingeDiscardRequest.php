<?php

namespace App\Http\Requests\Reporting;

use Illuminate\Foundation\Http\FormRequest;

class TaskPerformanceSettingeDiscardRequest extends FormRequest
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
            "delete_array.*.day"            => ["required", "integer", "between:0,6"],
            "delete_array.*.staff_id"       => ["required", "integer", "exists:staffs,id"],
            "delete_array.*.period"         => ["required", "date_format:H:i:s"]
        ];
    }
}
