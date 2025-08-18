<?php

namespace App\Http\Resources\Leave;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'rec_id'     => $this->rec_id,
            'eng_name'   => $this->leave_records->staff ? $this->leave_records->staff->eng_name : 'Unknown',
            'jp_name'    => $this->leave_records->staff ? $this->leave_records->staff->jp_name : 'Unknown',
            'leave_type' => $this->leave_type,
            'day_count'  => $this->day_count,
            'status'     => $this->status,
            'leave_date' => $this->leave_date,
            'duration'   => $this->duration,
            'reason'     => $this->reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
