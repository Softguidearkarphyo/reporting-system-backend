<?php

namespace App\Http\Requests\Staff;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StaffCreateRequest extends FormRequest
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
            'staff_no'        => ['nullable', 'string', 'max:30', Rule::unique('staffs', 'eng_name')],
            'eng_name'        => ['required', 'string', 'max:40', Rule::unique('staffs', 'eng_name')],
            'jp_name'         => ['required', 'string', 'max:40', Rule::unique('staffs', 'jp_name')],
            'username'        => ['required', 'string', 'max:40', Rule::unique('staffs', 'username')],
            'password'        => ['required', 'string', 'min:6', 'max:255'],
            'address'         => ['required', 'string', 'max:250'],
            'ph_number'       => ['nullable', 'string', 'max:100'],
            'position'        => ['required', 'integer', 'between:0,255'],
            'role'            => ['required', 'integer', 'between:0,255'],
            'email'           => ['nullable', 'string', 'email', 'max:255', Rule::unique('staffs', 'email')],
            'permanent_date'  => ['nullable', 'date'],
            'ref_person'      => ['nullable', 'string', 'max:40'],
            'ref_ph_number'   => ['nullable', 'string', 'max:100'],
            'project'         => ['nullable', 'integer', 'between:0,255'],
            'sort_key'        => ['nullable', 'integer', 'between:0,255'],
        ];
    }
}
