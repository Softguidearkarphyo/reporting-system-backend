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
            'staff_id'    => 'required|exists:staffs,id',
            
            'leave_date'  => 'nullable|required_without:multi_date',
            'multi_date'  => 'nullable|required_without:leave_date|array',
            'multi_date.*'=> 'nullable|string',
            'duration'    => 'required', 
            
            'reason'      => 'nullable|string',
        ];
    }
}
