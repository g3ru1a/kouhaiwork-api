<?php

namespace App\Http\Controllers;

use App\Http\Resources\MangaSearchResource;
use App\Models\Manga;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function searchManga(Request $request)
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
}
