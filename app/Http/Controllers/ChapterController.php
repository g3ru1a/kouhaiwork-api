<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChapterResource;
use App\Jobs\UploadChapterPagesJob;
use App\Models\Chapter;
use App\Models\Group;
use App\Models\Manga;
use App\Models\Page;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ChapterController extends Controller
{
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
            ->where(function($query) use ($s) {
                $query->where('number', 'like', '%' . $s . '%')
                ->orWhere('name', 'like', '%' . $s . '%')
                ->orWhere('volume', 'like', '%' . $s . '%')
                ->orWhereHas('manga', function ($q) use ($s){
                    $q->where('title', 'like', '%' . $s . '%')
                        ->orWhere('alternative_titles', 'like', '%' . $s . '%');
                });
            })
            ->whereIn('group_id', $groups)->get();
        return count($chaps) > 0 ? $chaps : response()->json(['message' => 'Chapter not found'], 422);
    }

    public function getChapter($id){
        $groups = [];
        foreach (Auth::user()->ownedGroups as $group) {
            array_push($groups, $group->id);
        }
        $chap = Chapter::with('pages', 'manga', 'manga.cover')->withCount('pages')->find($id);
        return $chap ? $chap : response()->json(['message' => 'Chapter not found'], 422);
    }

    public function upload(Request $request){
        //validate input
        $this->validate($request,
            [
                'manga_id' => 'required|string',
                'volume' => 'string',
                'chapter' => ['required','numeric', Rule::unique('chapters', 'number')->where(function($query) use ($request) {
                    return $query->where('manga_id', $request->manga_id)->whereNull('deleted_at');
                })],
                'chapter_name' => 'string',
                'group_id' => 'string',
            ]);
        //get manga obj
        $manga = Manga::find($request->manga_id);
        if ($manga == null) return response()->json(['manga_id' => ['The manga id field is required.']], 422);
        //create chapter obj
        $chapter = new Chapter([
            'volume' => $request->volume,
            'number' => $request->chapter,
            'name' => $request->chapter_name,
        ]);
        $manga->chapters()->save($chapter);
        //add pages
        //return chapter obj
        try {
            //Get group if provided
            if ($request->group_id) {
                $group = Group::find($request->group_id);
                if ($group) {
                    $group->chapters()->save($chapter);
                    $chapter->refresh();
                }
            }
            return response()->json(['chapter' => $chapter]);
        } catch (\Exception $e) {
            $chapter->delete();
            throw $e;
            // return response()->json(['pages' => 'Something went wrong while uploading pages.', 'ex' => $e], 422);
        }
    }

    public function addPages(Request $request){
        //validate input
        $this->validate(
            $request,
            [
                'chapter_id' => 'required|string',
                'order' => 'required|json',
                'pages' => 'required',
                'pages.*' => 'max:10240|mimes:jpg,jpeg,png,gif',
                'replace' => 'boolean',
            ]
        );
        $chapter = Chapter::find($request->chapter_id);
        if(!isset($chapter)) return response()->json(['error'=>['message'=>'Could not find chapter.']], 422);
        $manga = Manga::find($chapter->manga_id);
        if (!isset($manga)) return response()->json(['error' => ['message' => 'Could not find series.']], 422);
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
            return response()->json(['message' => 'Upload Successfull.']);
        } catch (\Exception $e) {
            throw $e;
            // return response()->json(['pages' => 'Something went wrong while uploading pages.', 'ex' => $e], 422);
        }
    }

    public function update(Request $request, $id)
    {
        //validate input
        $this->validate(
            $request,
            [
                'manga_id' => 'required|string',
                'volume' => 'string',
                'chapter' => ['required', 'numeric', Rule::unique('chapters', 'number')->where(function ($query) use ($request, $id) {
                    return $query->where('manga_id', $request->manga_id)->where('id', '!=', $id)->whereNull('deleted_at');
                })],
                'chapter_name' => 'string',
                'group_id' => 'string',
                'order' => 'json',
                'pages.*' => 'image|max:10240'
            ]
        );
        //get manga obj
        $manga = Manga::find($request->manga_id);
        if ($manga == null) return response()->json(['manga_id' => ['The manga id field is required.']], 422);
        //create chapter obj
        $chapter = Chapter::find($id);
        $chapter->volume = $request->volume;
        $chapter->number = $request->chapter;
        $chapter->name = $request->chapter_name;
        $chapter->save();
        //add pages
        //return chapter obj
        if($request->order){
            // $pages = $request->file('pages');
            // $order = json_decode($request->order);
            // if ($request->hasFile('pages')) {
            //     $next_id = null;
            //     for ($i = count($order) - 1; $i >= 0; $i--) {
            //         $page = MediaController::uploadPage($pages[$order[$i]], $next_id, 'chapters/' . $manga->title . '/' . $chapter->number, $next_id !== null ? false : true);
            //         $chapter->pages()->save($page);
            //         $next_id = $page->id;
            //     }
            // }
            if ($request->hasFile('pages')) {
                $chapter->pages()->delete();
                $seriesName = substr($manga->title, 0, 60);
                $pages_paths = [];
                foreach ($request->file('pages') as $page) {
                    $path = $seriesName . '-' . $chapter->number . '/' . $page->getClientOriginalName();
                    // return response()->json(['e'=> $path], 422);
                    $upath = Storage::disk('public')->put($path, $page);
                    array_push($pages_paths, $upath);
                }

                // return response()->json(['e' => $pages_paths], 422);
                $task = (new UploadChapterPagesJob($pages_paths, $request->order, $manga, $chapter))->onQueue('chapters');
                dispatch($task);
            }
        }
        //Get group if provided
        if ($request->group_id) {
            $group = Group::find($request->group_id);
            // return $request->group_id;
            if ($group) {
                $chapter->group()->dissociate();
                $chapter->group()->associate($group);
                $chapter->save();
                $chapter->refresh();
            }
        }
        return $chapter;
    }

    public function delete(Request $request, $id){
        $chapter = Chapter::find($id);
        if (!$chapter) return response()->json(['status' => 'error', 'message' => 'Could not find chapter.'], 422);
        try {
            $chapter->delete();
            return response()->json(['status' => 'success', 'message' => 'Chapter Deleted Successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function get($id){
        $chapter = Chapter::with('pages','manga', 'manga.cover')->where('uploaded', true)->find($id);
        $next = Chapter::where('manga_id', $chapter->manga_id)->where('uploaded', true)
            ->where('number', '>', $chapter->number)->orderBy('number', 'asc')->first();

        $prev = Chapter::where('manga_id', $chapter->manga_id)->where('uploaded', true)
            ->where('number', '<', $chapter->number)->orderBy('number', 'desc')->first();
        if($chapter){

            return response()->json([
                'chapter' => ChapterResource::make($chapter),
                'next_id' => $next ? $next->id : null,
                'prev_id' => $prev ? $prev->id : null,
            ]);

        }else return response()->json(['message' => 'Could not find the specified chapter in our database.']);
    }
}
