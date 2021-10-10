<?php

namespace App\Services;

use App\Exceptions\ModelNotFoundException;
use App\Http\Controllers\MediaController;
use App\Http\Resources\MangaCappedResource;
use App\Http\Resources\MangaResource;
use App\Http\Resources\ResponseResource;
use App\Models\Manga;
use App\Models\MangaDemographic;
use App\Models\MangaGenre;
use App\Models\MangaTheme;
use Countable;
use Illuminate\Database\Eloquent\Collection;

class MangaService
{
    private $fullOpt = [
        'cover', 'genres', 'themes', 'demographics', 'authors', 'artists', 'chapters', 'chapters.groups'
    ];
    /** @var Collection $result*/
    private $mangas, $result, $request;

    /**
     *  @param Manga $mangas
    */
    public function __construct($mangas, $request = null)
    {
        $this->mangas = $mangas;
        $this->request = $request;
    }

    public static function select($id){
        $manga = Manga::find($id);
        throw_if($manga === null, new ModelNotFoundException('Series'));
        return new MangaService($manga);
    }

    public static function make($request){
        try {
            $manga = Manga::create([
                'title' => $request->title,
                'synopsis' => $request->synopsis,
                'status' => $request->status,
                'origin' => $request->origin,
                'alternative_titles' =>
                    $request->alternative_titles ? json_decode($request->alternative_titles) : null,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
        return new MangaService($manga, $request);
    }

    public static function hasChapters(){
        $mangas = Manga::whereHas('chapters', function ($query) {
            $query->where('uploaded', true);
        });
        return new MangaService($mangas);
    }

    public static function notDeleted()
    {
        $mangas = Manga::whereNull('deleted_at');
        return new MangaService($mangas);
    }

    public function delete(){
        $this->detachRelations();
        // $this->removeCover();
        $this->mangas->delete();
        return ResponseResource::make('Series Deleted Successfully');
    }

    public function update($request){
        $this->request = $request;
        $this->mangas->fill([
            'title' => $request->title,
            'synopsis' => $request->synopsis,
            'status' => $request->status,
            'origin' => $request->origin,
            'alternative_titles' =>
            $request->alternative_titles ? json_decode($request->alternative_titles) : null,
        ])->save();
        return $this;
    }

    public function updateRelations(){
        $this->detachRelations();
        $this->attachRelations();
        return $this;
    }

    public function attachCover()
    {
        if($this->request->cover){
            if ($this->mangas->cover) {
                $this->mangas->cover()->delete();
            }
            $cover = MediaController::upload($this->request, 'cover', 'covers');
            $this->mangas->cover()->save($cover);
        }
        return $this;
    }

    public function attachRelations(){
        $this->attachRelation($this->request->genres, $this->mangas->genres(), MangaGenre::class);
        $this->attachRelation($this->request->themes, $this->mangas->themes(), MangaTheme::class);
        $this->attachRelation($this->request->demographics,
            $this->mangas->demographics(), MangaDemographic::class);
        $this->attachRelation($this->request->authors, $this->mangas->authors(), Author::class);
        $this->attachRelation($this->request->artists, $this->mangas->artists(), Artist::class);
        return $this;
    }

    private function attachRelation($req_data, $relation, $model_class){
        if ($req_data) {
            $relation->detach();
            foreach (json_decode($req_data) as $genre) {
                $g = $model_class::find($genre->id);
                if ($g) {
                    $relation->attach($g);
                }
            }
        }
    }

    public function result(){
        $this->result = $this->mangas->refresh();
        return $this;
    }

    public function detachRelations(){
        $this->mangas->genres()->detach();
        $this->mangas->themes()->detach();
        $this->mangas->demographics()->detach();
        $this->mangas->artists()->detach();
        $this->mangas->authors()->detach();
    }

    public function withEverything(){
        $this->mangas = $this->mangas->with($this->fullOpt);
        return $this;
    }

    public function withCover()
    {
        $this->mangas = $this->mangas->with('cover');
        return $this;
    }

    public function latest(){
        $this->result = $this->mangas->orderBy('updated_at', 'desc')->first();
        return $this;
    }

    public function all()
    {
        $this->result = $this->mangas->orderBy('updated_at', 'desc')->get();
        return $this;
    }

    public function take($count)
    {
        $this->result = $this->mangas->orderBy('updated_at', 'desc')->take($count)->get();
        return $this;
    }

    public function find($id)
    {
        $this->result = $this->mangas->find($id);
        return $this;
    }

    public function first($count = 1){
        if($count > 1) $this->result = $this->mangas->take($count)->get();
        else $this->result = $this->mangas->first();
        return $this;
    }

    public function toResource(){
        throw_if($this->result === null, new ModelNotFoundException('Series'));
        if($this->result instanceof Collection){
            return MangaResource::collection($this->result);
        }else return MangaResource::make($this->result);
    }

    public function toCapResource()
    {
        throw_if($this->result === null, new ModelNotFoundException('Series'));
        if ($this->result instanceof Collection) {
            return MangaCappedResource::collection($this->result);
        } else return MangaCappedResource::make($this->result);
    }
}
