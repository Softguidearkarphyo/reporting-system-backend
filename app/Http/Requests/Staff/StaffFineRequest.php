<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StaffFineRequest extends FormRequest
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
            'id'       => 'nullable|integer',
            'month'    => 'nullable|integer|between:1,12',
            'year'     => 'nullable|integer|digits:4',

            'staff_id' => 'sometimes|required|integer|exists:staffs,id',
            'date'     => 'sometimes|required|date',
            'amount'   => 'sometimes|required|numeric|min:0',
            'count'    => 'sometimes|required|integer|min:1',
            'status'   => 'nullable|integer',
        ];
    }
}