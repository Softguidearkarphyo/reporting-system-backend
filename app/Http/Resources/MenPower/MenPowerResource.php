<?php

namespace App\Http\Resources\MenPower;

use App\Http\Resources\Project\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenPowerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'cd'            => $this->cd,
            'eng_name'      => $this->eng_name,
            'jp_name'       => $this->jp_name,
            'days'          => $this->days,
            'men'           => $this->men,
            'hours'         => $this->hours,
        ];
    }
}
