<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Author;
use App\Models\Manga;
use App\Models\MangaDemographic;
use App\Models\MangaGenre;
use App\Models\MangaTheme;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    private $manga_opt = ['cover', 'genres', 'themes', 'demographics', 'groups', 'authors', 'artists', 'chapters', 'chapters.group'];
    public function index(){
        return Manga::with($this->manga_opt)->get()->first();
    }

    public function all(){
        return Manga::with('cover')->whereHas('chapters')->get();
    }
    public function allAdmin()
    {
        return Manga::with('cover')->whereNull('deleted_at')->get();
    }

    public function week(){
        return Manga::with('cover', 'chapters')->whereHas('chapters', function($query){
            $query->whereBetween('updated_at', [Carbon::now()->startOfWeek(Carbon::MONDAY), Carbon::now()->endOfWeek(Carbon::SUNDAY)]);
        })->get();
    }

    public function latest(){
        return Manga::with($this->manga_opt)->whereHas('chapters')->get()->last();
    }

    public function get($id) {
        $manga = Manga::with($this->manga_opt)->find($id);
        $grps = [];
        foreach ($manga->chapters as $chap) {
            if($chap->group){
                if(!in_array($chap->group->name, $grps)){
                    array_push($grps, $chap->group->name);
                }
            }
        }
        $manga->groups_arr = $grps;
        return $manga ? $manga : response()->json(['message'=>'Could not find the specified manga in our database.']);
    }

    public function chapters($id) {
        return Manga::with(['chapters.pages'])->findOrFail($id)->chapters;
    }

    public function search($search){
        $s = str_replace('_', ' ', strtolower($search));
        $manga = Manga::where('title', 'LIKE', '%' . $s . '%')
            ->orWhere('alternative_titles', 'LIKE', '%' . $s . '%')
            ->with('cover', 'genres')
            ->withCount('chapters')->get();
        return count($manga) != 0 ? $manga : response()->json(['message' => 'Could not find the specified manga in our database.']);
    }

    public function store(Request $request){
        $this->validate($request, [
            'title' => 'required|string',
            'synopsis' => 'required|string',
            'status' => 'required|string',
            'origin' => 'required|string',
            'cover' => 'required|image',
            'alternative_titles' => 'json',
            'genres' => 'json',
            'themes' => 'json',
            'demographics' => 'json',
            'authors' => 'json',
            'artists' => 'json',
        ]);
        try {
            $manga = new Manga();
            $manga->title = $request->title;
            $manga->synopsis = $request->synopsis;
            $manga->status = $request->status;
            $manga->origin = $request->origin;
            if($request->alternative_titles) $manga->alternative_titles = json_decode($request->alternative_titles);
            if($manga->save()){
                $cover = MediaController::upload($request, 'cover', 'covers');
                $manga->cover()->save($cover);
                $manga = MangaController::connectRelations($request, $manga);
                return $manga;
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    private static function connectRelations($request, $manga){
        if($request->genres){
            $manga->genres()->detach();
            foreach (json_decode($request->genres) as $genre) {
                $g = MangaGenre::find($genre->id);
                if($g){
                    $manga->genres()->attach($g);
                }
            }
        }
        if ($request->themes) {
            $manga->themes()->detach();
            foreach (json_decode($request->themes) as $theme) {
                $t = MangaTheme::find($theme->id);
                if ($t) {
                    $manga->themes()->attach($t);
                }
            }
        }
        if ($request->demographics) {
            $manga->demographics()->detach();
            foreach (json_decode($request->demographics) as $demo) {
                $d = MangaDemographic::find($demo->id);
                if ($d) {
                    $manga->demographics()->attach($d);
                }
            }
        }
        if ($request->authors) {
            $manga->authors()->detach();
            foreach (json_decode($request->authors) as $author) {
                $a = Author::find($author->id);
                if ($a) {
                    $manga->authors()->attach($a);
                }
            }
        }
        if ($request->artists) {
            $manga->artists()->detach();
            foreach (json_decode($request->artists) as $artist) {
                $a = Artist::find($artist->id);
                if ($a) {
                    $manga->artists()->attach($a);
                }
            }
        }
        $manga_opt = ['cover', 'genres', 'themes', 'demographics', 'groups', 'authors', 'artists', 'chapters'];
        return Manga::with($manga_opt)->find($manga->id);
    }

    private static function detachRelations($manga){
        $manga->genres()->detach();
        $manga->themes()->detach();
        $manga->demographics()->detach();
        $manga->artists()->detach();
        $manga->authors()->detach();
    }

    public function update(Request $request, $id){
        $this->validate($request, [
            'title' => 'required|string',
            'synopsis' => 'required|string',
            'status' => 'required|string',
            'origin' => 'required|string',
            'cover' => 'image',
            'alternative_titles' => 'json',
            'genres' => 'json',
            'themes' => 'json',
            'demographics' => 'json',
            'authors' => 'json',
            'artists' => 'json',
        ]);
        try {
            $manga = Manga::findOrFail($id);
            $manga->title = $request->title;
            $manga->synopsis = $request->synopsis;
            $manga->status = $request->status;
            $manga->origin = $request->origin;
            if ($request->alternative_titles) {
                $manga->alternative_titles = json_decode($request->alternative_titles);
            }else $manga->alternative_titles = [];
            if ($manga->save()) {
                if($request->cover){
                    $manga->cover()->delete();
                    $cover = MediaController::upload($request, 'cover', 'covers');
                    $manga->cover()->save($cover);
                }
                MangaController::detachRelations($manga);
                $manga = MangaController::connectRelations($request, $manga);
                return $manga;
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
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
