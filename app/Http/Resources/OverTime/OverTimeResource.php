<?php

namespace App\Http\Resources\OverTime;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OverTimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'staff_id'   => $this->staff_id,
            'eng_name'   => $this->staff ? $this->staff->eng_name : 'Unknown',
            'jp_name'    => $this->staff ? $this->staff->jp_name : 'Unknown',
            'ot_date'    => $this->ot_date,
            'ot_time'    => $this->ot_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
