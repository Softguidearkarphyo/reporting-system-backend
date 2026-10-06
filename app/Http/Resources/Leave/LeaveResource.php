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
        'id'         => $this->id,
        'rec_id'     => $this->rec_id,
        
        // Staff Info
        'eng_name'   => $this->leave_records?->staff?->eng_name ?? 'Unknown',
        'jp_name'    => $this->leave_records?->staff?->jp_name ?? 'Unknown',
        
        // Leave Info
        'leave_type' => $this->leave_type,
        'day_count'  => $this->day_count,
        'status'     => $this->status,
        'leave_date' => $this->leave_date,
        'duration'   => $this->duration,
        'reason'     => $this->reason,
        
        // Nested Leave Record Object
        'leave_record_summary' => [
            'year'           => $this->leave_records?->year,
            'permanent_date' => $this->leave_records?->permanent_date,
            'carry_leaves'   => $this->leave_records?->carry_leaves ?? 0,
            'remain_leaves'  => $this->leave_records?->remain_leaves ?? 0,
            'first_annual'   => $this->leave_records?->first_annual ?? 0,
            'second_annual'  => $this->leave_records?->second_annual ?? 0,
            'total_used'     => $this->leave_records?->total_used ?? 0,
            'total_leaves'   => $this->leave_records?->total_leaves ?? 0,
            'accumulated_hours'   => $this->leave_records?->accumulated_hours ?? 0,
        ],

        // Timestamps
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
        'deleted_at' => $this->deleted_at,
    ];
}
}
