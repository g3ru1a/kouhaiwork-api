<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\ArtistRequest;
use App\Http\Resources\ArtistResource;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArtistController extends Controller
{
    private $cacheKey = 'manga-artists';

    public function index()
    {
        if(Cache::has($this->cacheKey)){
            return response()->json(json_decode(Cache::get($this->cacheKey)));
        }else {
            $data = Artist::all();
            $col = ArtistResource::collection($data);
            Cache::put($this->cacheKey, json_encode($col->response()->getData()), 60 * 60 * 24);
            return $col;
        }
    }

    public function store(ArtistRequest $request)
    {
        try {
            $artist = Artist::create([
                'name' => $request->name,
            ]);
            Cache::forget($this->cacheKey);
            return ArtistResource::make($artist);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update(ArtistRequest $request, $id)
    {
        try {
            $artist = Artist::find($id);
            throw_if($artist === null, new ModelNotFoundException('Artist'));
            $artist->name = $request->name;
            if ($artist->save()) {
                Cache::forget($this->cacheKey);
                return ArtistResource::make($artist);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $artist = Artist::find($id);
            throw_if($artist === null, new ModelNotFoundException('Artist'));
            if ($artist->delete()) {
                Cache::forget($this->cacheKey);
                return response()->json(['data' => ['message' => 'Successfully Deleted Artist']]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
