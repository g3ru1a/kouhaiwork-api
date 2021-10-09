<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\ChapterEditRequest;
use App\Http\Requests\ChapterPageRequest;
use App\Http\Requests\ChapterRequest;
use App\Http\Resources\ChapterResource;
use App\Http\Resources\ResponseResource;
use App\Jobs\UploadChapterPagesJob;
use App\Models\Chapter;
use App\Models\Manga;
use App\Services\ChapterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ChapterController extends Controller
{
    

    public function store(ChapterRequest $request){
        return ChapterService::make($request)->attachGroups($request->groups)->toResource();
    }

    public function update(ChapterEditRequest $request, $id)
    {
        return ChapterService::find($id)->update($request)->attachGroupsClean($request->groups)->toResource();
    }

    public function delete(Request $request, $id)
    {
        return ChapterService::find($id)->delete();
    }

    public function addPages(ChapterPageRequest $request)
    {
        $chapter = Chapter::find($request->chapter_id);
        throw_if($chapter === null, new ModelNotFoundException('Chapter'));
        $manga = Manga::find($chapter->manga_id);
        throw_if($manga === null, new ModelNotFoundException('Series'));
        try {
            if ($request->hasFile('pages')) {
                $seriesName = substr($manga->title, 0, 60);
                $pages = $request->file('pages');
                $pages_paths = [];
                foreach ($pages as $page) {
                    $path = $seriesName . '-' . $chapter->number . '/' . $page->getClientOriginalName();
                    // return response()->json(['e'=> $path], 422);
                    $upath = Storage::disk('public')->put($path, $page);
                    array_push($pages_paths, $upath);
                }
                // return response()->json(['e' => $pages_paths], 422);
                $task = (new UploadChapterPagesJob($pages_paths, $request->order, $manga, $chapter))->onQueue('chapters');
                dispatch($task);
            }
            return ResponseResource::make('Upload Dispatched.');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function get($id){
        if(Cache::has('chapter-'.$id)){
            return Cache::get('chapter-'.$id);
        }
        $chapter = Chapter::with('pages','manga', 'manga.cover')->where('uploaded', true)->find($id);
        throw_if($chapter === null, new ModelNotFoundException('Chapter'));

        $next = Chapter::where('manga_id', $chapter->manga_id)->where('uploaded', true)
            ->where('number', '>', $chapter->number)->orderBy('number', 'asc')->first();

        $prev = Chapter::where('manga_id', $chapter->manga_id)->where('uploaded', true)
            ->where('number', '<', $chapter->number)->orderBy('number', 'desc')->first();

        $r = response()->json([
            'chapter' => ChapterResource::make($chapter),
            'next_id' => $next ? $next->id : null,
            'prev_id' => $prev ? $prev->id : null,
        ]);
        Cache::put('chapter-' . $id, $r, 60 * 60 * 24);
        return $r;
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'search' => 'required|string'
        ]);
        $s = str_replace('_', ' ', strtolower($request->search));
        $groups = [];
        foreach (Auth::user()->ownedGroups as $group) {
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
            ->whereIn('group_id', $groups)->get();
        return count($chaps) > 0 ? $chaps : response()->json(['message' => 'Chapter not found'], 422);
    }

    public function getChapter($id)
    {
        $chap = Chapter::with('manga', 'manga.cover')->withCount('pages')->find($id);
        throw_if($chap === null, new ModelNotFoundException('Chapter'));
        return ChapterResource::make($chap);
    }

    // public function getCheck($id){
    //     $chapter = Chapter::find($id);
    //     if($chapter){
    //         return response()->json(['uploaded'=>$chapter->uploaded]);
    //     } else return response()->json(['message' => 'Could not find the specified chapter in our database.']);
    // }
}
