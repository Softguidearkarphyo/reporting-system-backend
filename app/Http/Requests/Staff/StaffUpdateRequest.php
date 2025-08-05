<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StaffUpdateRequest extends FormRequest
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
            'id'              => ['required', 'integer', 'exists:staffs,id'],
            'staff_no'        => ['nullable', 'string', 'max:30'],
            'eng_name'        => ['required', 'string', 'max:40'],
            'jp_name'         => ['required', 'string', 'max:40'],
            'username'        => ['required', 'string', 'max:40'],
            'password'        => ['nullable', 'string', 'min:6', 'max:255'],
            'address'         => ['required', 'string', 'max:250'],
            'ph_number'       => ['nullable', 'string', 'max:100'],
            'position'        => ['required', 'integer', 'between:0,255'],
            'role'            => ['required', 'integer', 'between:0,255'],
            'email'           => ['nullable', 'string', 'email', 'max:255'],
            'permanent_date'  => ['nullable', 'date'],
            'ref_person'      => ['nullable', 'string', 'max:40'],
            'ref_ph_number'   => ['nullable', 'string', 'max:100'],
            'project'         => ['nullable', 'array'],
            'project.*'       => ['integer'],
            'sort_key'        => ['nullable', 'integer', 'between:0,255'],
        ];
    }
}
