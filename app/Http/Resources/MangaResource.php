<?php

namespace App\Http\Resources;

use App\Models\Manga;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class MangaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $groups = Manga::getGroups($this->id);
        return [
            'id' => $this->id,
            'title' => $this->title,
            'synopsis' => $this->synopsis,
            'status' => $this->status,
            'origin' => $this->origin,
            'created_by' => User::find($this->created_by),
            'cover' => $this->cover->url,
            'groups' => $groups ? GroupCompactResource::collection($groups) : null,
            'genres' => $this->when(count($this->genres) > 0, $this->tagToArray($this->genres)),
            'themes' => $this->when(count($this->themes) > 0, $this->tagToArray($this->themes)),
            'demographics' => $this->when(count($this->demographics) > 0, $this->tagToArray($this->demographics)),
            'authors' => $this->when(count($this->authors) > 0, $this->tagToArray($this->authors)),
            'artists' => $this->when(count($this->artists) > 0, $this->tagToArray($this->artists)),
            'alternative_titles' => $this->when(($this->alternative_titles != null && count($this->alternative_titles) > 0), $this->alternative_titles),
            'chapters' => ChapterCompactResource::collection($this->chapters),
        ];
    }

    private function tagToArray($tags)
    {
        $ar = [];
        foreach ($tags as $tag) {
            array_push($ar, ['id' => $tag->id, 'name'=>$tag->name]);
        }
        return $ar;
    }
}
