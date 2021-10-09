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
    private $cacheKey = 'manga-authors';

    public function index()
    {
        if (Cache::has($this->cacheKey)) {
            return response()->json(json_decode(Cache::get($this->cacheKey)));
        } else {
            $data = Author::all();
            $col = AuthorResource::collection($data);
            Cache::put($this->cacheKey, json_encode($col->response()->getData()), 60 * 60 * 24);
            return $col;
        }
    }

    public function store(AuthorRequest $request)
    {
        try {
            $author =Author::create([
                'name' => $request->name
            ]);
            Cache::forget($this->cacheKey);
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
                Cache::forget($this->cacheKey);
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
                Cache::forget($this->cacheKey);
                return response()->json(['data' => ['message' => 'Successfully Deleted Author']]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
