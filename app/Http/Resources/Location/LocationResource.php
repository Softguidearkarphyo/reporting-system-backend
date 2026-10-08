<?php

namespace App\Http\Resources\Location;

use App\Http\Resources\Staff\StaffResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'id'            => $this->id,
            'staff_id'      => $this->staff_id,
            'lat'           => $this->lat,
            'lon'           => $this->lon,
            'staff'         => $this->when(
                isset($data['staff']),
                new StaffResource($this->staff),
            ),
            'allow_meter'   => $this->allow_meter,
            'device_uuid'   => $this->device_uuid,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'deleted_at'    => $this->deleted_at,
        ];
    }
}
