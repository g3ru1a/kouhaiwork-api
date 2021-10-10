<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChapterInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'volume' => $this->when($this->volume != null, $this->volume),
            'number' => $this->number,
            'name' => $this->when($this->name != null, $this->name),
            'groups' => GroupResource::collection($this->groups),
            'updated_at' => $this->updated_at,
        ];
    }
}
