<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\GenreRequest;
use App\Http\Resources\GenreResource;
use App\Models\MangaGenre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GenreController extends Controller
{
    private $cacheKey = 'manga-genres';

    public function index(){
        if (Cache::has($this->cacheKey)) {
            return response()->json(json_decode(Cache::get($this->cacheKey)));
        } else {
            $data = MangaGenre::all();
            $col = GenreResource::collection($data);
            Cache::put($this->cacheKey, json_encode($col->response()->getData()), 60 * 60 * 24);
            return $col;
        }
    }

    public function store(GenreRequest $request){
        try {
            $mg = MangaGenre::create([
                'name' => $request->name
            ]);
            Cache::forget($this->cacheKey);
            return GenreResource::make($mg);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update(GenreRequest $request, $id){
        try {
            $mg = MangaGenre::find($id);
            throw_if($mg === null, new ModelNotFoundException('Genre'));
            $mg->name = $request->name;
            if($mg->save()) {
                Cache::forget($this->cacheKey);
                return GenreResource::make($mg);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function delete(Request $request, $id){
        try {
            $mg = MangaGenre::find($id);
            throw_if($mg === null, new ModelNotFoundException('Genre'));
            if($mg->delete()) {
                Cache::forget($this->cacheKey);
                return response()->json(['data'=> ['message'=>'Successfully Deleted Genre']]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
