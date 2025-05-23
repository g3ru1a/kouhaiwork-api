<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChapterResource extends JsonResource
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
            'manga_id' => $this->manga_id,
            'groups' => $this->groups ? GroupCompactResource::collection($this->groups) : null,
            'manga' => MangaCompactResource::make($this->manga),
            'pages' => PageResource::collection($this->pages),
        ];
    }
}
