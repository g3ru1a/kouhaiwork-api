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
            'alternative_titles' => 'string',
            'status' => 'required|string',
            'origin' => 'required|string',
            'cover' => 'required|image'
        ]);
        try {
            $manga = new Manga();
            $manga->title = $request->title;
            $manga->synopsis = $request->synopsis;
            $manga->alternative_titles = $request->alternative_titles;
            $manga->status = $request->status;
            $manga->origin = $request->origin;
            if($manga->save()){
                $cover = MediaController::upload($request, 'cover', 'covers');
                $manga->cover()->save($cover);
                return response()->json(['status' => 'success', 'message' => 'Manga Created Successfully.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $id){
        
    }

    public function delete(Request $request, $id){
        $manga = Manga::find($id);
        if(!$manga) return response()->json(['status' => 'error', 'message' => 'Could not find manga with id.'], 422);
        try {
            $manga->delete();
            return response()->json(['status' => 'success', 'message' => 'Manga Deleted Successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
