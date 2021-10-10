<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\AuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuthorController extends Controller
{
    public function index()
    {
        $key = 'manga-authors';
        if (Cache::has($key)) {
            return response()->json(json_decode(Cache::get($key)));
        } else {
            $data = Author::all();
            $col = AuthorResource::collection($data);
            Cache::put($key, json_encode($col->response()->getData()), 60 * 60 * 24);
            return $col;
        }
    }

    public function store(AuthorRequest $request)
    {
        try {
            $author =Author::create([
                'name' => $request->name
            ]);
            return AuthorResource::make($author);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update(AuthorRequest $request, $id)
    {
        try {
            $author = Author::find($id);
            throw_if($author === null, new ModelNotFoundException('Author'));
            $author->name = $request->name;
            if ($author->save()) {
                return AuthorResource::make($author);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $author = Author::find($id);
            throw_if($author === null, new ModelNotFoundException('Author'));
            if ($author->delete()) {
                return response()->json(['data' => ['message' => 'Successfully Deleted Author']]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
