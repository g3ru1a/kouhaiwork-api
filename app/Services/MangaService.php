<?php

namespace App\Services;

use App\Exceptions\BadRequestException;
use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Http\Controllers\MediaController;
use App\Http\Resources\MangaCompactResource;
use App\Http\Resources\MangaResource;
use App\Http\Resources\ResponseResource;
use App\Models\Artist;
use App\Models\Author;
use App\Models\Manga;
use App\Models\MangaDemographic;
use App\Models\MangaGenre;
use App\Models\MangaTheme;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MangaService extends BaseService{
    public static function getCacheKeyStatic($id)
    {
        return 'manga-'.$id;
    }

    public static function getResourceClass()
    {
        return MangaResource::class;
    }

    public static function getResourceCompactClass()
    {
        return MangaCompactResource::class;
    }

    /**
     * @return Manga
     */
    public static function getModelClass()
    {
        return Manga::class;
    }

    public function postDataChanges()
    {
        parent::postDataChanges();
        Cache::forget('mangas-all-user-'.AuthService::user()->id);
        Cache::forget('mangas-all-has_chapter-user-' . AuthService::user()->id);
        Cache::forget('mangas-all-');
        Cache::forget('mangas-all-has_chapter');
    }

    /**
     * @param string|null $condition
     * @param boolean $checkOwner
     * @return self
     */
    public static function all($condition, $checkOwner = false){

        $cacheKey = 'mangas-all-'.($condition ? $condition : '');
        if($checkOwner){
            $cacheKey = $cacheKey.'-user-'.AuthService::user()->id;
        }

        if(Cache::has($cacheKey)) return new self(Cache::get($cacheKey));

        $manga = Manga::with('cover')->whereNull('deleted_at');
        if($checkOwner && !AuthService::user()->isAdmin()){
            $manga = $manga->where('created_by', AuthService::user()->id);
        }
        if($condition == 'has_chapters'){
            $manga = $manga->whereHas('chapters', function ($query) {
                $query->where('uploaded', true);
            });
        }
        $manga = $manga->get();
        Cache::put($cacheKey, $manga,);

        return new self($manga);
    }

    /**
     * @param int $id
     * @param boolean $mustBeCreatedByUser
     * @return self
     */
    public static function find($id, $mustBeCreatedByUser = false){
        $instance = parent::find($id);
        throw_if(
            $mustBeCreatedByUser && $instance->getSingleModel()->created_by !== AuthService::user()->id,
            new InvalidParameterException('series id'));
        return $instance;
    }

    /**
     * @param Array $request
     * @return self
     */
    public static function make($dataKeyValueArray, $ppdc = false){
        if(isset($dataKeyValueArray['alternative_title'])){
            $dataKeyValueArray['alternative_titles'] = json_decode($dataKeyValueArray['alternative_title']);
        }
        $dataKeyValueArray['created_by'] = AuthService::user()->id;
        $instance = parent::make($dataKeyValueArray);
        return $instance;
    }

    /**
     * @param Array $request
     * @return self
     */
    public function update($dataKeyValueArray){
        if (isset($dataKeyValueArray['alternative_title'])) {
            $dataKeyValueArray['alternative_titles'] = json_decode($dataKeyValueArray['alternative_title']);
        }
        $instance = parent::update($dataKeyValueArray);
        return $instance;
    }

    /**
     * @return JsonResponse
     */
    public function delete(){
        $this->getSingleModel()->chapters()->delete();
        Storage::disk('public')->delete($this->getSingleModel()->cover->url);
        parent::delete();
        return ResponseResource::make('Deleted.');
    }

    /**
     * @param Request $request
     * @param boolean $mustAttach
     * @return self
     */
    public function attachCover($request, $mustAttach = false){
        if(!$request->has('cover')){
            if($mustAttach){
                $this->getSingleModel()->delete();
                throw new BadRequestException('cover');
            }
            return;
        }
        $modelInstance = $this->getSingleModel();
        $modelInstance->cover()->delete();
        $media = MediaController::upload($request->file('cover'), 'cover/' . $modelInstance->id);
        $modelInstance->cover()->save($media);
        $this->setSingleModel($modelInstance);
        return $this;
    }

    /**
     * @return self
     */
    public function detachRelations()
    {
        $modelInstance = $this->getSingleModel();
        $modelInstance->genres()->detach();
        $modelInstance->themes()->detach();
        $modelInstance->demographics()->detach();
        $modelInstance->artists()->detach();
        $modelInstance->authors()->detach();
        $this->setSingleModel($modelInstance);
        return $this;
    }

    public function attachRelations($dataKeyValueArray){
        $modelInstance = $this->getSingleModel();
        $this->attachRelation($dataKeyValueArray, 'genres', $modelInstance->genres(), MangaGenre::class);
        $this->attachRelation($dataKeyValueArray, 'themes', $modelInstance->themes(), MangaTheme::class);
        $this->attachRelation($dataKeyValueArray, 'demographics', $modelInstance->demographics(),
            MangaDemographic::class);
        $this->attachRelation($dataKeyValueArray, 'authors', $modelInstance->authors(), Author::class);
        $this->attachRelation($dataKeyValueArray, 'artists', $modelInstance->artists(), Artist::class);
        $this->setSingleModel($modelInstance);
        return $this;
    }

    private function attachRelation($data, $key,$relation,$class){
        if(!isset($data[$key])) return;
        $relation->detach();
        foreach(json_decode($data[$key]) as $d){
            $i = $class::find($d->id);
            throw_if($i === null, new ModelNotFoundException(class_basename($class)));
            $relation->attach($i);
        }
    }

}

