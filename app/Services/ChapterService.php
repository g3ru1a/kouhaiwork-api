<?php

namespace App\Services;

use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Http\Resources\ChapterNoPagesResource;
use App\Http\Resources\ChapterResource;
use App\Http\Resources\ResponseResource;
use App\Models\Chapter;
use App\Models\Group;
use App\Models\Manga;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChapterService {

    public $chapter;

    public function __construct($chapter)
    {
        $this->chapter = $chapter;
    }

    public static function toChapterCollection($chapterServices){
        $col = new Collection();
        foreach($chapterServices as $cs){
            $col->add($cs->chapter);
        }
        return $col;
    }

    public static function checkCache($key){
        if (Cache::has($key)) {
            $chapters = Cache::pull($key); //TODO change to get instead of pull
            if(is_array($chapters)){
                $services = [];
                foreach ($chapters as $chap) {
                    array_push($services, new ChapterService($chap));
                }
                return $services;
            }else return new ChapterService($chapters); 
        }else return false;
    }

    /**
     * Grab chapter with special conditions
     * 
     * @param string $type
     * @param int $count
     * @return ChapterService|ChapterSerivce[]
     */
    public static function grab($type, $count = 1){
        switch($type){
            case 'latest':
                $chapter = Chapter::where('uploaded', '1')->orderBy('updated_at', 'desc');
                $chapter = $count === 1 ? $chapter->first() : $chapter->take($count)->get();
                break;
            case 'recent':
                $chapter = Chapter::where('uploaded', '1')->orderBy('updated_at', 'desc')->get()->unique('manga_id');
                $chapter = $count === 1 ? $chapter->first() : $chapter->take($count);
                break;
            default:
                throw new InvalidParameterException('Invalid grab type');
                break;
        }
        if($count == 1) {
            return new ChapterService($chapter);
        }else{
            $services = [];
            foreach ($chapter as $chap) {
                array_push($services, new ChapterService($chap));
            }
            return $services;
        }
    }

    /**
     * Store Chapter/Chapters in cache
     * 
     * @param string $key
     * @param ChapterService|ChapterService[] $chapters
     * 
     * @return void
     */
    public static function cache($key, $chapters, $cacheImages = false){
        if(is_array($chapters)){
            $chaps = [];
            foreach($chapters as $chap){
                if($cacheImages){
                    $chap->chapter->pages = ChapterService::cachePages($chap->chapter->pages, $key);
                    $chap->chapter->manga->cover = ChapterService::cacheCover($chap->chapter->manga->cover, $key);
                }
                array_push($chaps, $chap->chapter);
            }
            Cache::put($key, $chaps);
        }else {
            if ($cacheImages) {
                $chapters->chapter->pages = ChapterService::cachePages($chapters->chapter->pages,$key);
                $chapters->chapter->manga->cover = ChapterService::cacheCover($chapters->chapter->manga->cover, $key);
            }
            Cache::put($key, $chapters->chapter, 10);
        }
    }

    private static function cacheCover($cover, $key)
    {
        if($cover->media == null) return $cover;
        $pref = env('APP_URL', 'http://localhost:8000') . '/storage/cache/covers/' . $key . '/';
        Log::info('Caching cover at: ' . $pref);
        $url = $cover->media->url;
        $contents = file_get_contents($url);
        $name = substr($url, strrpos($url, '/') + 1);
        $path = 'cache/covers/' . $key . '/' . $name;
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $contents);
        }
        $cover->media->url = $pref . $name;
        return $cover;
    }

    private static function cachePages($pages,$key){
        $pref = env('APP_URL', 'http://localhost:8000').'/storage/cache/pages/' . $key . '/';
        Log::info('Caching '.count($pages).' pages at: ' .$pref);
        for($i = 0; $i < count($pages); $i++){
            $url = $pages[$i]->media->url;
            $contents = file_get_contents($url);
            $name = substr($url, strrpos($url,'/')+1);
            $path = 'cache/pages/' . $key . '/' . $name;
            if(!Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, $contents);
            }
            $pages[$i]->media->url = $pref . $name;
        }
        return $pages;
    }

    public static function get($id){
        $chapter = Chapter::with('pages', 'manga', 'manga.cover')->where('uploaded', true)->find($id);
        throw_if($chapter === null, new ModelNotFoundException('Chapter'));
        return new ChapterService($chapter);
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
        Cache::forget('latest-chapter');
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

        Cache::forget('latest-chapter');
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
        Cache::forget('latest-chapter');
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

    public function toNoPageResource()
    {
        return ChapterNoPagesResource::make($this->chapter);
    }

    

}