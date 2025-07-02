<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id'             => $this->id,
            'eng_name'       => $this->eng_name,
            'jp_name'        => $this->jp_name,
            'username'       => $this->username,
            'address'        => $this->address,
            'ph_number'      => $this->ph_number,
            'position'       => $this->position,
            'role'           => $this->role,
            'email'          => $this->email,
            'perment_date'   => $this->perment_date,
            'ref_person'     => $this->ref_person,
            'ref_ph_number'  => $this->ref_ph_number,
            'project'        => $this->project,
            'sort_key'       => $this->sort_key,
        ];
    }
}
