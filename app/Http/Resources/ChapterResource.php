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
            'number' => $this->number,
            'manga_id' => $this->manga_id,
            'pages' => PageResource::collection($this->pages),
            'manga' => [
                'title' => $this->manga->title,
                'cover' => $this->manga->cover->url,
            ]
        ];
    }
}
