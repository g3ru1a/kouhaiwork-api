<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    private $manga_opt = ['cover', 'genres', 'themes', 'demographics', 'groups', 'authors', 'artists', 'chapters'];
    public function index(){
        return Manga::with($this->manga_opt)->get()->first();
    }

    public function all(){
        return Manga::with($this->manga_opt)->get();
    }

    public function week(){
        return Manga::with($this->manga_opt)->where('id', '<', '3')->get();
    }

    public function latest(){
        return Manga::with($this->manga_opt)->get()->last();
    }

    public function get($id) {
        $manga = Manga::with($this->manga_opt)->find($id);
        return $manga ? $manga : response()->json(['message'=>'Could not find the specified manga in our database.']);
    }

    public function chapters($id) {
        return Manga::with(['chapters.pages'])->findOrFail($id)->chapters;
    }

    public function search($search){
        $s = str_replace('_', ' ', strtolower($search));
        $manga = Manga::where('title', 'LIKE', '%' . $s . '%')->orWhere('alternative_titles', 'LIKE', '%' . $s . '%')->with('cover')->get();
        return count($manga) != 0 ? $manga : response()->json(['message' => 'Could not find the specified manga in our database.']);
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
