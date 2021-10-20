<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\ChapterPageRequest;
use App\Http\Requests\ChapterRequest;
use App\Http\Resources\ChapterCompactResource;
use App\Http\Resources\ChapterResource;
use App\Models\Chapter;
use App\Models\Group;
use App\Services\ChapterService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ChapterController extends Controller
{
    public function relinkGroups(){
        $chapters = Chapter::whereNull('deleted_at')->where('uploaded', true)->get();
        foreach($chapters as $c){
            if($c->group_id === null) continue;
            $g = Group::findOrFail($c->group_id);
            $c->groups()->attach($g);
            $c->group_id = null;
            $c->save();
        }
    }

    public function recent()
    {
        $key = 'chapters-recent';
        if (Cache::has($key)) {
            return ChapterCompactResource::collection(Cache::get($key));
        }
        $chapters = DB::table('chapters')->where('deleted_at', null)->select(['id', 'number', 'manga_id'])->orderBy('number', 'desc')->groupBy('manga_id')
            ->get();
        $chapters = $chapters->slice(0, 8);
        $chapterIDS = array_column($chapters->toArray(), 'id');
        $chapters = Chapter::findMany($chapterIDS);
        Cache::put($key, $chapters);
        return ChapterCompactResource::collection($chapters);
    }

    public function latest(){
        $key = 'latest-chapter';
        if (Cache::has($key)) {
            return ChapterResource::make(Cache::get($key));
        }
        $chapter = Chapter::orderBy('updated_at', 'desc')->first();
        Cache::put($key, $chapter);
        return ChapterResource::make($chapter);
    }

    public function get($id)
    {
        $key = 'full-chapter-'.$id;
        if(Cache::has($key)){
            return response()->json(Cache::get($key));
        }
        $chapter = Chapter::with('pages', 'manga', 'manga.cover')->where('uploaded', true)->find($id);
        throw_if($chapter === null, new ModelNotFoundException('Chapter'));

        $next = Chapter::where('manga_id', $chapter->manga_id)->where('uploaded', true)
        ->where('number', '>', $chapter->number)->orderBy('number', 'asc')->first();

        $prev = Chapter::where('manga_id', $chapter->manga_id)->where('uploaded', true)
        ->where('number', '<', $chapter->number)->orderBy('number', 'desc')->first();

        $res = [
            'chapter' => ChapterResource::make($chapter),
            'next_id' => $next ? $next->id : null,
            'prev_id' => $prev ? $prev->id : null,
        ];
        Cache::put($key, $res);
        return response()->json($res);
    }

    public function getEdit($id){
        return ChapterService::find($id)->toResource();
    }

    public function store(ChapterRequest $request){
        return ChapterService::make($request->toArray())->toResource();
    }

    public function update(ChapterRequest $request, $id)
    {
        return ChapterService::find($id)->update($request->toArray())->toResource();
    }

    public function addPages(ChapterPageRequest $request){
        return ChapterService::find($request->chapter_id)
            ->addPages($request, $request->replace ? $request->replace : false)->toResource();
    }

    public function delete(Request $request, $id){
        return ChapterService::find($id)->delete();
    }

}
