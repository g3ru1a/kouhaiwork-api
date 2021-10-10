<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\AnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\ResponseResource;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Http;

class AnnouncementsController extends Controller
{
    public function index(){
        $posts = Post::orderBy('updated_at', 'desc')->take(10)->get();
        foreach ($posts as $post) {
            $post->body = htmlspecialchars_decode($post->body);
        }
        return AnnouncementResource::collection($posts);
    }

    public function store(AnnouncementRequest $request){
        try {
            $ann = Post::create([
                'title' => $request->title,
                'body' => htmlspecialchars($request->body),
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
        Http::post(env("FRONT_CACHE_API").'/flush/announcements');
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
        } catch (\Exception $e) {
            throw $e;
        }
        Http::post(env("FRONT_CACHE_API") . '/flush/announcements');
        return AnnouncementResource::make(Post::find($id));
    }

    public function delete(Request $request, $id)
    {
        $ann = Post::find($id);
        throw_if($ann === null, new ModelNotFoundException('Announcement'));
        try {
            $ann->delete();
        } catch (\Exception $e) {
            throw $e;
        }
        Http::post(env("FRONT_CACHE_API") . '/flush/announcements');
        return ResponseResource::make('Announcement Deleted Successfully.');
    }
}
