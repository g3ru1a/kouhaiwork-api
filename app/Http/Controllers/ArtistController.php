<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    public function index()
    {
        return Artist::all();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:artists'
        ]);
        try {
            $mg = new Artist();
            $mg->name = $request->name;
            if ($mg->save()) {
                return $mg;
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:artists'
        ]);
        try {
            $mg = Artist::findOrFail($id);
            $mg->name = $request->name;
            if ($mg->save()) {
                return $mg;
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $mg = Artist::findOrFail($id);
            if ($mg->delete()) {
                return response()->json(['status' => 'success', 'message' => 'Successfully Deleted Artist']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
