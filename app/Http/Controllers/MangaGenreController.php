<?php

namespace App\Http\Controllers;

use App\Models\MangaGenre;
use Illuminate\Http\Request;

class MangaGenreController extends Controller
{
    public function index(){
        return MangaGenre::all();
    }

    public function store(Request $request){
        $this->validate($request, [
            'name'=>'required|string|unique:manga_genres'
        ]);
        try {
            $mg = new MangaGenre();
            $mg->name = $request->name;
            if($mg->save()) {
                return $mg;
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>'error', 'message'=>$e->getMessage()], 422);
        }
        
    }

    public function update(Request $request, $id){
        $this->validate($request, [
            'name'=>'required|string|unique:manga_genres'
        ]);
        try {
            $mg = MangaGenre::findOrFail($id);
            $mg->name = $request->name;
            if($mg->save()) {
                return $mg;
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>'error', 'message'=>$e->getMessage()], 422);
        }
    }

    public function delete(Request $request, $id){
        try {
            $mg = MangaGenre::findOrFail($id);
            if($mg->delete()){
                return response()->json(['status'=>'success', 'message'=>'Successfully Deleted Genre']);
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>'error', 'message'=>$e->getMessage()], 422);
        }
    }
}
