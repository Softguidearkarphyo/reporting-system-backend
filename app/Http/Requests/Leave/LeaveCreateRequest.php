<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class LeaveCreateRequest extends FormRequest
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
            'leave_type'  => ['required'],
            'start_date'  => ['required', 'nullable', 'date'],
            'end_date'    => ['required', 'nullable', 'date'],
            'leave_date'  => ['required', 'nullable', 'date'],
            'duration'    =>  ['required', 'nullable', 'string'],
            'reason'      =>  ['required', 'string'],
        ];
    }
}
