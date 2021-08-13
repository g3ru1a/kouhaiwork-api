<?php

namespace App\Http\Controllers;

use App\Models\MangaTheme;
use Illuminate\Http\Request;

class MangaThemeController extends Controller
{
    public function index(){
        return MangaTheme::all();
    }

    public function store(Request $request){
        $this->validate($request, [
            'name'=>'required|string|unique:manga_themes'
        ]);
        try {
            $mg = new MangaTheme();
            $mg->name = $request->name;
            if($mg->save()){
                return response()->json(['status'=>'success', 'message'=>'Successfully Created Theme']);
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>'error', 'message'=>$e->getMessage()]);
        }
        
    }

    public function update(Request $request, $id){
        $this->validate($request, [
            'name'=>'required|string|unique:manga_themes'
        ]);
        try {
            $mg = MangaTheme::findOrFail($id);
            $mg->name = $request->name;
            if($mg->save()){
                return response()->json(['status'=>'success', 'message'=>'Successfully Updated Theme']);
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>'error', 'message'=>$e->getMessage()]);
        }
    }

    public function delete(Request $request, $id){
        try {
            $mg = MangaTheme::findOrFail($id);
            if($mg->delete()){
                return response()->json(['status'=>'success', 'message'=>'Successfully Deleted Theme']);
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>'error', 'message'=>$e->getMessage()]);
        }
    }
}
