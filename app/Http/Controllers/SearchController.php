<?php

namespace App\Http\Controllers;

use App\Http\Resources\DemographicResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\MangaSearchResource;
use App\Http\Resources\ThemeResource;
use App\Models\MangaDemographic;
use App\Models\MangaGenre;
use App\Models\MangaTheme;
use App\Models\Chapter;
use App\Models\Manga;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function mangaParams()
    {
        if(Cache::has('search-parameters')){
            return response()->json(Cache::get('search-parameters'));
        }
        $genres = GenreResource::collection(MangaGenre::all());
        $demographics = DemographicResource::collection(MangaDemographic::all());
        $themes = ThemeResource::collection(MangaTheme::all());
        $params = ['g' => $genres, 'd' => $demographics, 't' => $themes];
        Cache::put('search-parameters', $params);
        return response()->json($params);
    }

    public function manga(Request $request)
    {
        $this->validate($request, [
            'search' => 'string',
            'tags' => 'json'
        ]);

        $manga = Manga::with('cover', 'genres')->whereHas('chapters')->withCount('chapters');
        $tags = json_decode($request->tags);
        if ($tags) {
            if (count($tags->genres) > 0) {
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
        }
        $s = strtolower($request->search);
        if ($s != '') {
            $manga->where('title', 'LIKE', '%' . $s . '%')
                ->orWhere('alternative_titles', 'LIKE', '%' . $s . '%');
        }
        $manga = $manga->get();
        $mResults = collect();
        if (isset($genres)) {
            foreach ($manga as $m) {
                $mg = $m->genres->map(function ($g) {
                    return $g->id;
                })->toArray();
                if (count($mg) > 0 && !array_diff($genres, $mg)) {
                    $mResults = $mResults->push($m);
                }
            }
            $manga = $mResults;
            $mResults = collect();
        }
        if (isset($themes)) {
            foreach ($manga as $m) {
                $mt = $m->themes->map(function ($t) {
                    return $t->id;
                })->toArray();
                if (count($mt) > 0 && !array_diff($themes, $mt)) {
                    $mResults = $mResults->push($m);
                }
            }
            $manga = $mResults;
            $mResults = collect();
        }
        if (isset($demographics)) {
            foreach ($manga as $m) {
                $md = $m->demographics->map(function ($d) {
                    return $d->id;
                })->toArray();
                if (count($md) > 0 && !array_diff($demographics, $md)) {
                    $mResults = $mResults->push($m);
                }
            }
            $manga = $mResults;
            $mResults = collect();
        }
        if (count($manga) != 0) {
            return MangaSearchResource::collection($manga);
        } else return response()->json(['message' => 'Could not find the specified manga in our database.']);
    }

    public function chapters(Request $request)
    {
        $this->validate($request, [
            'search' => 'required|string'
        ]);
        $s = str_replace('_', ' ', strtolower($request->search));
        $groups = [];
        foreach (AuthService::user()->ownedGroups as $group) {
            array_push($groups, $group->id);
        }
        $chaps = Chapter::with('pages', 'manga', 'manga.cover')->withCount('pages')->where('uploaded', true)
            ->where(function ($query) use ($s) {
                $query->where('number', 'like', '%' . $s . '%')
                    ->orWhere('name', 'like', '%' . $s . '%')
                    ->orWhere('volume', 'like', '%' . $s . '%')
                    ->orWhereHas('manga', function ($q) use ($s) {
                        $q->where('title', 'like', '%' . $s . '%')
                            ->orWhere('alternative_titles', 'like', '%' . $s . '%');
                    });
            })
            ->get();
        $arr = [];
        foreach($chaps as $c){
            $g = array_column($c->groups->toArray(), 'id');
            if (array_intersect($g, $groups)) {
                array_push($arr, $c);
            }
        }
        return count($chaps) > 0 ? $chaps : response()->json(['message' => 'Chapter not found'], 422);
    }
}
