<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffFineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $request->all();
        return  [
            'id'                => $this->id,
            'staff_id'          => $this->staff_id,
            'staff_name'        => $this->staff->eng_name,
            'amount'            => $this->amount,
            'date'              => $this->date,
            'time'              => $this->time,
            'status'            => $this->status,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
