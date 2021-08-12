<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function index(){
        return Post::all();
    }

    public function store(Request $request){
        try {
            $post = new Post();
            $post->title = $request->title;
            $post->body = $request->body;
            if($post->save()){
                return response()->json(['status' => 'success', 'message' => 'Post Created Successfully.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
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

    public function destroy(Request $request, $id){
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
