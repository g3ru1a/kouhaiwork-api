<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MangaLatestResource extends JsonResource
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
            'title' => $this->title,
            'synopsis' => $this->synopsis,
            'status' => $this->status, 
            'latest_chapter' => [
                'id' => $this->chapters[count($this->chapters) - 1]->id,
                'number' => $this->chapters[count($this->chapters) - 1]->number,
            ],
            'tags' => $this->mapTags($this->genres, $this->themes, $this->demographics),
            'cover' => $this->cover->url,
        ];
    }

    private function mapTags($g, $t, $d){
        // return $g;
        $tags = [];
        foreach ($g as $gen) {
            array_push($tags, $gen->name);
        }
        foreach ($t as $th) {
            array_push($tags, $th->name);
        }
        foreach ($d as $demo) {
            array_push($tags, $demo->name);
        }
        return $tags;
    }
}
