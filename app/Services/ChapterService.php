<?php

namespace App\Services;

use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Http\Controllers\MediaController;
use App\Http\Resources\ChapterCompactResource;
use App\Http\Resources\ChapterResource;
use App\Models\Chapter;
use App\Models\Group;
use App\Models\Manga;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChapterService extends BaseService{


    public static function getCacheKeyStatic($id)
    {
        return 'chapter-' . $id;
    }

    public static function getResourceClass()
    {
        return ChapterResource::class;
    }

    public static function getResourceCompactClass()
    {
        return ChapterCompactResource::class;
    }

    /**
     * @return Chapter
     */
    public static function getModelClass()
    {
        return Chapter::class;
    }

    public function postDataChanges()
    {
        parent::postDataChanges();
        Cache::forget('latest-chapter');
        Cache::forget('chapters-recent');
        Cache::forget('full-chapter-'.$this->getSingleModel()->id);
    }

    public function addPages($request, $clear = false)
    {
        $manga = Manga::find($this->getSingleModel()->manga_id);
        throw_if($manga === null, new ModelNotFoundException('Manga'));

        $folder = 'pages/' . '[' . $manga->id . ']' . substr($manga->title, 0, 60) . '/[' .
            $this->getSingleModel()->id . ']' . $this->getSingleModel()->number;

        if($clear){
            $this->getSingleModel()->pages()->delete();
            MediaController::deleteDir(storage_path().'/app/public/'.$folder);
        }

        $pages = $request->file('pages');
        if (count($this->getSingleModel()->pages) == 0) $next_id = null;
        else $next_id = $this->getSingleModel()->pages->first()->id;
        Log::info('Page count: ' . count($pages));
        Log::info($request);
        try {
            for ($i = count($pages) - 1; $i >= 0; $i--) {
                // foreach ($pages as $page) {
                $page = $pages[$i];
                $p = MediaController::uploadPage($page, $next_id, $folder);
                $this->getSingleModel()->pages()->save($p);
                $next_id = $p->id;
            }
        } catch (\Throwable $th) {
            $this->delete();
            throw $th;
        }

        $this->getSingleModel()->uploaded = true;
        $this->getSingleModel()->save();
        $this->setSingleModel($this->getSingleModel()->refresh());
        $this->postDataChanges();
        return $this;
    }

    /**
     * @param int $id Model ID
     * @return self
     */
    public static function find($id){
        $instance = parent::find($id);
        //Make sure the user working with this chapter is either admin or owner of one of the groups marked on it.
        if(!AuthService::user()->isAdmin()){
            $found = false;
            foreach($instance->getSingleModel()->groups as $group){
                if($group->owner_id === AuthService::user()->id){
                    $found = true;
                    break;
                }
            }
            if(!$found) throw new InvalidParameterException('group id');
        }
        return $instance;
    }

    /**
     * @param array $dataKeyValueArray
     * @param boolean $preventPostDataChanges
     * @return self
     */
    public static function make($dataKeyValueArray, $preventPostDataChanges = false){
        $check = Chapter::whereNull('deleted_at')->where('number', $dataKeyValueArray['number'])
            ->where('manga_id',  $dataKeyValueArray['manga_id'])->get();
        throw_if($check && count($check) > 0, new InvalidParameterException('number'));

        $manga = Manga::find($dataKeyValueArray['manga_id']);
        throw_if($manga === null, new ModelNotFoundException('Manga'));
        $instance = parent::make($dataKeyValueArray, $preventPostDataChanges);
        try {
            $manga->chapters()->save($instance->getSingleModel());
            $groups = json_decode($dataKeyValueArray['groups']);
            foreach ($groups as $group_id) {
                $group = Group::find($group_id);
                throw_if($group === null, new ModelNotFoundException('Group'));
                throw_if($group->owner_id !== AuthService::user()->id, new InvalidParameterException('groups'));
                $group->chapters()->attach($instance->getSingleModel());
            }
            $instance->setSingleModel($instance->getSingleModel()->refresh());
        } catch (\Exception $e) {
            $instance->getSingleModel()->delete();
            throw $e;
        }
        return $instance;
    }

    /**
     * @param array $dataKeyValueArray
     * @return self
     */
    public function update($dataKeyValueArray)
    {
        $check = Chapter::whereNull('deleted_at')->where('id', '!=', $this->getSingleModel()->id)
            ->where('number', $dataKeyValueArray['number'])
            ->where('manga_id',  $dataKeyValueArray['manga_id'])->get();
        throw_if($check && count($check) > 0, new InvalidParameterException('number'));

        $manga = Manga::find($dataKeyValueArray['manga_id']);
        throw_if($manga === null, new ModelNotFoundException('Manga'));
        $instance = parent::update($dataKeyValueArray);
        try {
            $manga->chapters()->save($instance->getSingleModel());
            $instance->getSingleModel()->groups()->detach();
            $groups = json_decode($dataKeyValueArray['groups']);
            foreach ($groups as $group_id) {
                $group = Group::find($group_id);
                throw_if($group === null, new ModelNotFoundException('Group'));
                throw_if($group->owner_id !== AuthService::user()->id, new InvalidParameterException('groups'));
                $group->chapters()->attach($instance->getSingleModel());
            }
            $instance->setSingleModel($instance->getSingleModel()->refresh());
        } catch (\Exception $e) {
            $instance->getSingleModel()->delete();
            throw $e;
        }
        return $this;
    }

    public function delete(){
        //TODO Delete Pages
        parent::delete();
    }
}