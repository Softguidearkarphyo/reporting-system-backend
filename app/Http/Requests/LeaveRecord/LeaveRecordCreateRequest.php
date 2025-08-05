<?php

namespace App\Http\Requests\LeaveRecord;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class LeaveRecordCreateRequest extends FormRequest
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
            'staff_id'        => ['required', Rule::unique('leave_record', 'staff_id')],
            'permanent_date'  => ['required', 'date'],
            'remain_leaves'   => ['nullable'],
            'total_leaves'    => ['nullable'],
        ];
    }
}
