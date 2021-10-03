<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\ThemeRequest;
use App\Http\Resources\ThemeResource;
use App\Models\MangaTheme;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function index(){
        return ThemeResource::collection(MangaTheme::all());
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
