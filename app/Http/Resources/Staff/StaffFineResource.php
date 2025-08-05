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
        return [

            'id'                => $this->id,
            'staff_id'          => $this->staff_id,
            'date'              => $this->date,
            'time'              => $this->time,
            'amount'            => $this->amount,
            'status'            => $this->status,
            'staff'             => new StaffResource($this->staff),

        ];
    }
}
