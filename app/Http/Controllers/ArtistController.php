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
    public static function cacheUpdate()
    {
        Cache::forget('search-parameters');
        Cache::forget('manga-artists');
    }
    public function index()
    {
        $key = 'manga-artists';
        if(Cache::has($key)){
            return response()->json(json_decode(Cache::get($key)));
        }else {
            $data = Artist::all();
            $col = ArtistResource::collection($data);
            Cache::put($key, json_encode($col->response()->getData()), 60 * 60 * 24);
            return $col;
        }
    }

    public function store(ArtistRequest $request)
    {
        try {
            $artist = Artist::create([
                'name' => $request->name,
            ]);
            ArtistController::cacheUpdate();
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
                ArtistController::cacheUpdate();
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
                ArtistController::cacheUpdate();
                return response()->json(['data' => ['message' => 'Successfully Deleted Artist']]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
