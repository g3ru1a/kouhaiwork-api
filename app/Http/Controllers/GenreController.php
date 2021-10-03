<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\GenreRequest;
use App\Http\Resources\GenreResource;
use App\Models\MangaGenre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index(){
        return GenreResource::collection(MangaGenre::all());
    }

    public function store(GenreRequest $request){
        try {
            $mg = MangaGenre::create([
                'name' => $request->name
            ]);
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
            if($mg->delete()){
                return response()->json(['data'=> ['message'=>'Successfully Deleted Genre']]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
