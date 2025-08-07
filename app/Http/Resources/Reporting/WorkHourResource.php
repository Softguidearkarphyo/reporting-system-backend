<?php

namespace App\Http\Resources\Reporting;

use Illuminate\Http\Request;
use App\Http\Resources\Staff\StaffResource;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkHourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"            => $this->id,
            "staff_id"      => $this->staff_id,
            "periods"        => $this->period,
            'eng_name'      => $this->staff->eng_name,
            'jp_name'       => $this->staff->jp_name,
        ];
    }
}
