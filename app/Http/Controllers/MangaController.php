<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Author;
use App\Models\Chapter;
use App\Models\Manga;
use App\Models\MangaDemographic;
use App\Models\MangaGenre;
use App\Models\MangaTheme;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
    public function allR2()
    {
        return Manga::whereNull('deleted_at')->get();
    }

    public function getAdmin($id)
    {
        return Manga::with($this->manga_opt)->find($id);
    }

    public function week(){
        return Manga::with('cover')->whereHas('chapters')
        ->with(['chapters' => function ($q) {
            $q->orderBy('updated_at', 'desc');
        }])->whereNull('deleted_at')
        ->take(8)->get();
        // ->whereHas('chapters', function($query){
        //     $query->whereBetween('updated_at', [Carbon::now()->startOfWeek(Carbon::MONDAY), Carbon::now()->endOfWeek(Carbon::SUNDAY)]);
        // })
    }

    public function latest(){
        $lc = Chapter::whereNull('deleted_at')->orderBy('updated_at', 'desc')->get()->first();
        return Manga::with($this->manga_opt)->whereHas('chapters')->whereNull('deleted_at')
            ->find($lc->manga_id);
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

    public function search(Request $request){
        $this->validate($request, [
            'search' => 'string',
            'tags' => 'json'
        ]);

        $manga = Manga::with('cover', 'genres')->whereHas('chapters')->withCount('chapters');
        $tags = json_decode($request->tags);
        if(count($tags->genres) > 0){
            $genres = array_column($tags->genres, 'id');
        }
        if (count($tags->themes) > 0) {
            $themes = array_column($tags->themes, 'id');
            $manga->with('themes');
        }
        if (count($tags->demographics) > 0) {
            $demographics = array_column($tags->demographics, 'id');
            $manga->with('demographics');
        }
        if ($tags->status) {
            $status = $tags->status->id == 0 ? 'ongoing' : ($tags->status->id == 1 ? 'finished' : 'axed');
            $manga->where('status', $status);
        }
        $s = strtolower($request->search);
        if ($s != '') {
            $manga->where('title', 'LIKE', '%' . $s . '%')
                ->orWhere('alternative_titles', 'LIKE', '%' . $s . '%');
        }
        $manga = $manga->get();
        $mResults = collect();
        if(isset($genres)) {
            foreach($manga as $m){
                $mg = $m->genres->map(function ($g) { return $g->id; })->toArray();
                if(count($mg) > 0 && !array_diff($genres, $mg)) {
                    $mResults = $mResults->push($m);
                }
            }
            $manga = $mResults;
            $mResults = collect();
        }
        if (isset($themes)) {
            foreach ($manga as $m) {
                $mt = $m->themes->map(function ($t) { return $t->id; })->toArray();
                if (count($mt) > 0 && !array_diff($themes, $mt)) {
                    $mResults = $mResults->push($m);
                }
            }
            $manga = $mResults;
            $mResults = collect();
        }
        if (isset($demographics)) {
            foreach ($manga as $m) {
                $md = $m->demographics->map(function ($d) { return $d->id; })->toArray();
                if (count($md) > 0 && !array_diff($demographics, $md)) {
                    $mResults = $mResults->push($m);
                }
            }
            $manga = $mResults;
            $mResults = collect();
        }
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
            // return str_replace('https://s3.eu-west-1.amazonaws.com', '', $manga->cover->url);
            // return $manga->cover->url;
            $manga->delete();
            return response()->json(['status' => 'success', 'message' => 'Manga Deleted Successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
