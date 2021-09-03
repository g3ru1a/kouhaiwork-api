<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function index(){
        return Post::orderBy('updated_at', 'desc')->take(10)->get();
    }

    public function store(Request $request){
        $this->validate($request, [
            'title' => 'required|string',
            'body' => 'required|string',
        ]);
        try {
            $post = new Post();
            $post->title = $request->title;
            $post->body = $request->body;
            if($post->save()){
                return $post;
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $id){
        try {
            $post = Post::findOrFail($id);
            $post->title = $request->title;
            $post->body = $request->body;
            if($post->save()){
                return response()->json(['status' => 'success', 'message' => 'Post Updated Successfully.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function delete(Request $request, $id){
        try {
            $post = Post::findOrFail($id);
            if($post->delete()){
                return response()->json(['status' => 'success', 'message' => 'Post Deleted Successfully.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
