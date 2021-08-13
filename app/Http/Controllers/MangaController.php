<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    public function index(){
        return Manga::with('cover', 'genres', 'themes', 'demographics')->get()->last();
    }

    public function store(Request $request){
        $this->validate($request, [
            'title' => 'required|string',
            'synopsis' => 'required|string',
            'alternative_titles' => 'required|string',
            'status' => 'required|string',
            'origin' => 'required|string',
            'cover' => 'required|image'
        ]);
        try {
            $post = new Manga();
            $post->title = $request->title;
            $post->synopsis = $request->synopsis;
            $post->alternative_titles = $request->alternative_titles;
            $post->status = $request->status;
            $post->origin = $request->origin;
            if($post->save()){
                $cover = MediaController::upload($request, 'cover', 'covers');
                $post->cover()->save($cover);
                return response()->json(['status' => 'success', 'message' => 'Manga Created Successfully.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id){
        
    }

    public function delete(Request $request, $id){
        
    }
}
