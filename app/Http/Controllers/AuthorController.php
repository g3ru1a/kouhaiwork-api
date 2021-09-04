<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        return Author::all();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:authors'
        ]);
        try {
            $mg = new Author();
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
            'name' => 'required|string|unique:authors'
        ]);
        try {
            $mg = Author::findOrFail($id);
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
            $mg = Author::findOrFail($id);
            if ($mg->delete()) {
                return response()->json(['status' => 'success', 'message' => 'Successfully Deleted Author']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
