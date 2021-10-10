<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChapterNoPagesResource extends JsonResource
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
            'number' => $this->number,
            'manga_id' => $this->manga_id,
            'groups' => GroupResource::collection($this->groups),
            'manga' => MangaCappedResource::make($this->manga)
        ];
    }
}
