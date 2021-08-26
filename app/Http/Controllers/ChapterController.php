<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use App\Models\Page;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function upload(Request $request){
        //validate input
        $this->validate($request,
            [
                'volume' => 'string',
                'chapter' => 'required|string',
                'chapter_name' => 'string',
                'manga_id' => 'required|string',
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
                $page = MediaController::uploadPage($pages[$order[$i]], $next_id, 'chapters/'.$manga->name, $next_id !== null ? false : true);
                $chapter->pages()->save($page);
                $next_id = $page->id;
            }
        }
        return response()->json(['chapter' => $chapter]);
    }

    public function get($id){
        return Chapter::with('pages')->find($id);
    }
}
