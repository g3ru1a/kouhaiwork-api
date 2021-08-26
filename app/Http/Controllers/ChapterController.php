<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use App\Models\Page;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function upload(Request $request){
        //validate input
        $this->validate($request,
            [
                'manga_id' => 'required|string',
                'volume' => 'string',
                'chapter' => ['required','numeric', Rule::unique('chapters', 'number')->where(function($query) use ($request) {
                    return $query->where('manga_id', $request->manga_id);
                })],
                'chapter_name' => 'string',
                'order' => 'required|json',
                'pages' => 'required',
                'pages.*' => 'image'
            ]
        );
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
        $pages = $request->file('pages');
        $order = json_decode($request->order);
        if($request->hasFile('pages')){
            $next_id = null;
            for ($i=count($order)-1; $i >= 0; $i--) {
                $page = MediaController::uploadPage($pages[$order[$i]], $next_id, 'chapters/'.$manga->title.'/'.$chapter->number, $next_id !== null ? false : true);
                $chapter->pages()->save($page);
                $next_id = $page->id;
            }
        }
        return response()->json(['chapter' => $chapter]);
    }

    public function get($id){
        $chapter = Chapter::with('pages')->find($id);
        $next = Chapter::where('manga_id', $chapter->manga_id)->where('number', '>', $chapter->number)->first();
        $prev = Chapter::where('manga_id', $chapter->manga_id)->where('number', '<', $chapter->number)->first();
        return response()->json([
            'chapter' => $chapter,
            'next_id' => $next ? $next->id : null,
            'prev_id' => $prev ? $prev->id : null,
        ]);
    }
}
