<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\AnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\ResponseResource;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class AnnouncementsController extends Controller
{
    public function index(){
        $ck = 'announcements';
        if(Cache::has($ck)){
            return AnnouncementResource::collection(Cache::get($ck));
        }
        $posts = Post::orderBy('updated_at', 'desc')->take(10)->get();
        foreach ($posts as $post) {
            $post->body = htmlspecialchars_decode($post->body);
        }
        Cache::put($ck, $posts);
        return AnnouncementResource::collection($posts);
    }

    public function store(AnnouncementRequest $request){
        try {
            $ann = Post::create([
                'title' => $request->title,
                'body' => htmlspecialchars($request->body),
            ]);
            Cache::forget('announcements');
        } catch (\Exception $e) {
            throw $e;
        }

        return AnnouncementResource::make($ann);
    }

    public function update(AnnouncementRequest $request, $id)
    {
        $ann = Post::find($id);
        throw_if($ann === null, new ModelNotFoundException('Announcement'));
        try {
            $ann->fill([
                'title' => $request->title,
                'body' => htmlspecialchars($request->body),
            ])->save();
            Cache::forget('announcements');
        } catch (\Exception $e) {
            throw $e;
        }
        return AnnouncementResource::make(Post::find($id));
    }

    public function delete(Request $request, $id)
    {
        $ann = Post::find($id);
        throw_if($ann === null, new ModelNotFoundException('Announcement'));
        try {
            $ann->delete();
            Cache::forget('announcements');
        } catch (\Exception $e) {
            throw $e;
        }
        return ResponseResource::make('Announcement Deleted Successfully.');
    }
}
