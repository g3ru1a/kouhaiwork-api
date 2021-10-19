<?php

namespace App\Http\Controllers;

use App\Http\Requests\MangaRequest;
use App\Http\Resources\ChapterCompactResource;
use App\Models\Manga;
use App\Services\AuthService;
use App\Services\MangaService;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    public function recent(){
        return MangaService::take(10)->toResourceCompact();
    }

    public function all(){
        return MangaService::all('has_chapters')->toResourceCompact();
    }

    public function get($id)
    {
        return MangaService::find($id)->toResource();
    }

    public function allEdit()
    {
        return MangaService::all(null, true)->toResourceCompact();
    }

    public function getEdit($id)
    {
        return MangaService::find($id, !AuthService::user()->isAdmin())->toResource();
    }

    public function getChapters($id) {
        return ChapterCompactResource::collection(Manga::findOrFail($id)->chapters);
    }

    public function store(MangaRequest $request){
        return MangaService::make($request->toArray())->attachCover($request, true)
            ->attachRelations($request->toArray())->toResource();
    }

    public function update(MangaRequest $request, $id){
        return MangaService::find($id, !AuthService::user()->isAdmin())->update($request->toArray())->attachCover($request)
            ->detachRelations()->attachRelations($request->toArray())->toResource();
    }

    public function delete(Request $request, $id){
        return MangaService::find($id, !AuthService::user()->isAdmin())->delete();
    }
}
