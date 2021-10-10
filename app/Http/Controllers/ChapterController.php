<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\ChapterEditRequest;
use App\Http\Requests\ChapterPageRequest;
use App\Http\Requests\ChapterRequest;
use App\Http\Resources\ChapterNoPagesResource;
use App\Http\Resources\ChapterResource;
use App\Http\Resources\ResponseResource;
use App\Jobs\UploadChapterPagesJob;
use App\Models\Chapter;
use App\Models\Manga;
use App\Services\ChapterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChapterController extends Controller
{
    public function latest(){
        $ch = ChapterService::checkCache('latest-chapter');
        if($ch !== false){
            return $ch->toNoPageResource();
        }else{
            $ch = ChapterService::grab('latest');
            ChapterService::cache('latest-chapter', $ch);
            return $ch->toNoPageResource();
        }
    }

    public function recent(){
        $key = 'recent-chapter';
        $chapters = ChapterService::checkCache($key);
        if ($chapters !== false) {
            return ChapterNoPagesResource::collection(ChapterService::toChapterCollection($chapters));
        } else {
            $chapters = ChapterService::grab('recent', 8);
            ChapterService::cache($key, $chapters);
            return ChapterNoPagesResource::collection(ChapterService::toChapterCollection($chapters));
        }
        // if (Cache::has($key)) {
        //     return ChapterResource::collection(Cache::pull($key));
        // }
        // $latestChapter = Chapter::where('uploaded', '1')->orderBy('updated_at', 'desc')->get()->unique('manga_id')->take(10);
        // Cache::put($key, $latestChapter);
        // return ChapterResource::collection($latestChapter);
    }

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

                    Log::info('Trying to upload: ' . $page->getClientOriginalName());
                    $path = $seriesName . '-' . $chapter->number . '/' . $page->getClientOriginalName();
                    // return response()->json(['e'=> $path], 422);
                    $upath = Storage::disk('public')->put($path, $page);
                    array_push($pages_paths, $upath);
                }

                Log::info($pages_paths);
                // return response()->json(['e' => $pages_paths], 422);
                $task = (new UploadChapterPagesJob($pages_paths, $request->order, $manga, $chapter))->onQueue('chapters');
                dispatch($task);
            }else throw new BadRequestException('Missing pages.');
            return ResponseResource::make('Upload Dispatched.');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function get($id){
        if(Cache::has('chapter-'.$id)){
            return Cache::pull('chapter-'.$id);
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
