<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\ThemeRequest;
use App\Http\Resources\ThemeResource;
use App\Models\MangaTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ThemeController extends Controller
{
    public function index(){
        $key = 'manga-themes';
        if (Cache::has($key)) {
            return response()->json(json_decode(Cache::get($key)));
        } else {
            $data = MangaTheme::all();
            $col = ThemeResource::collection($data);
            Cache::put($key, json_encode($col->response()->getData()), 60 * 60 * 24);
            return $col;
        }
    }

    public function store(ThemeRequest $request){
        try {
            $mt = MangaTheme::create([
                'name' => $request->name
            ]);
            return ThemeResource::make($mt);
        } catch (\Exception $e) {
            throw $e;
        }
        
    }

    public function update(ThemeRequest $request, $id){
        try {
            $mt = MangaTheme::find($id);
            throw_if($mt === null, new ModelNotFoundException('Theme'));
            $mt->name = $request->name;
            if($mt->save()) {
                return ThemeResource::make($mt);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function delete(Request $request, $id){
        try {
            $mt = MangaTheme::find($id);
            throw_if($mt === null, new ModelNotFoundException('Theme'));
            if($mt->delete()){
                return response()->json(['data'=>['message'=>'Successfully Deleted Theme']]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
