<?php

namespace App\Http\Resources\LeaveRecord;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'staff_id'       => $this->staff_id,
            'eng_name'       => $this->staff ? $this->staff->eng_name : 'Unknown',
            'jp_name'        => $this->staff ? $this->staff->jp_name : 'Unknown',
            'year'           => $this->year,
            'permanent_date' => $this->permanent_date,
            'carry_leaves'   => $this->carry_leaves,
            'remain_leaves'  => $this->remain_leaves,
            'first_annual'   => $this->first_annual,
            'second_annual'  => $this->second_annual,
            'total_leaves'   => $this->total_leaves,
            'total_used'     => $this->total_used,
            'leaves'         => $this->whenLoaded('leaves', function () {
                return $this->leaves->map(function ($leave) {
                    return [
                        'id'         => $leave->id,
                        'rec_id'     => $leave->rec_id,
                        'leave_date' => $leave->leave_date,
                        'duration'   => $leave->duration,
                        'day_count'  => $leave->day_count,
                        'reason'     => $leave->reason,
                        'leave_type' => $leave->leave_type,
                        'created_at' => $leave->created_at,
                        'updated_at' => $leave->updated_at,
                    ];
                });
            }),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'deleted_at'     => $this->deleted_at,
        ];
    }
}