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
            'staff_id'  => $this->staff_id,
            'eng_name' => $this->staff ? $this->staff->eng_name : 'Unknown',
            'jp_name' => $this->staff ? $this->staff->jp_name : 'Unknown',
            'permanent_date' => $this->permanent_date,
            'remain_leaves' => $this->remain_leaves,
            'total_leaves' => $this->total_leaves,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
