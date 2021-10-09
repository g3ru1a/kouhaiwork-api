<?php

namespace App\Services;

use App\Exceptions\ModelNotFoundException;
use App\Http\Resources\ChapterResource;
use App\Http\Resources\ResponseResource;
use App\Models\Chapter;
use App\Models\Group;
use App\Models\Manga;

class ChapterService {

    private $chapter;

    public function __construct($chapter)
    {
        $this->chapter = $chapter;
    }

    public static function make($request){
        $manga = ChapterService::getManga($request->manga_id);
        try {
            $chapter = Chapter::create([
                'volume' => $request->volume,
                'number' => $request->number,
                'name' => $request->name,
            ]);
            $manga->chapters()->save($chapter);
        } catch (\Exception $e) {
            $chapter->delete();
            throw $e;
        }
        return new ChapterService($chapter);
    }

    public static function find($id){
        $chapter = Chapter::find($id);
        throw_if($chapter === null, new ModelNotFoundException('Chapter'));
        return new ChapterService($chapter);
    }

    private static function getManga($id)
    {
        $manga = Manga::find($id);
        throw_if($manga === null, new ModelNotFoundException('Series'));
        return $manga;
    }

    public function delete(){
        try {
            $this->chapter->delete();
        } catch (\Exception $e) {
            throw $e;
        }
        return ResponseResource::make('Chapter Deleted.');
    }

    public function update($request){
        $manga = ChapterService::getManga($request->manga_id);
        $manga->chapters()->save($this->chapter);
        $this->chapter->fill([
            'volume' => $request->volume,
            'number' => $request->number,
            'name' => $request->name,
        ])->save();
        $this->chapter->refresh();
        return $this;
    }

    public function attachGroupsClean($groups)
    {
        $this->chapter->groups()->detach();
        $this->attachGroups($groups);
        return $this;
    }

    public function attachGroups($groups){
        foreach ($groups as $group_id) {
            $group = Group::find($group_id);
            try {
                throw_if($group === null, new ModelNotFoundException('Group'));
                $this->chapter->groups()->attach($group);
            } catch (\Exception $e) {
                $this->chapter->delete();
                throw $e;
            }
        }
        $this->chapter->refresh();
        return $this;
    }

    public function toResource(){
        return ChapterResource::make($this->chapter);
    }

}