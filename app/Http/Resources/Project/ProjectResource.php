<?php

namespace App\Http\Resources\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id'        => $this->id,
            'code'      => $this->code,
            'eng_name'  => $this->eng_name,
            'jp_name'   => $this->jp_name,
        ];
    }
}
