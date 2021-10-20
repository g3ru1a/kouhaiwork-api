<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChapterCompactResource extends JsonResource
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
            'uploaded' => $this->uploaded,
            'groups' => $this->groups ? GroupCompactResource::collection($this->groups) : null,
            'updated_at' => $this->updated_at,
            'manga' => [
                'id' => $this->manga->id,
                'cover' => $this->manga->cover->url
            ]
        ];
    }
}
