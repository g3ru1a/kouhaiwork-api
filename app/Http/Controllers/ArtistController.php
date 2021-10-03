<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\ArtistRequest;
use App\Http\Resources\ArtistResource;
use App\Models\Artist;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    public function index()
    {
        return ArtistResource::collection(Artist::all());
    }

    public function store(ArtistRequest $request)
    {
        try {
            $artist = Artist::create([
                'name' => $request->name,
            ]);
            return ArtistResource::make($artist);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update(ArtistRequest $request, $id)
    {
        try {
            $artist = Artist::find($id);
            throw_if($artist === null, new ModelNotFoundException('Artist'));
            $artist->name = $request->name;
            if ($artist->save()) {
                return ArtistResource::make($artist);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $artist = Artist::find($id);
            throw_if($artist === null, new ModelNotFoundException('Artist'));
            if ($artist->delete()) {
                return response()->json(['data' => ['message' => 'Successfully Deleted Artist']]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
