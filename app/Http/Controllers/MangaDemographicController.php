<?php

namespace App\Http\Controllers;

use App\Models\MangaDemographic;
use Illuminate\Http\Request;

class MangaDemographicController extends Controller
{
    public function index(){
        return MangaDemographic::all();
    }

    public function store(Request $request){
        $this->validate($request, [
            'name'=>'required|string|unique:manga_demographics'
        ]);
        try {
            $mg = new MangaDemographic();
            $mg->name = $request->name;
            if($mg->save()) {
                return $mg;
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>'error', 'message'=>$e->getMessage()], 422);
        }
        
    }

    public function update(Request $request, $id){
        $this->validate($request, [
            'name'=>'required|string|unique:manga_demographics'
        ]);
        try {
            $mg = MangaDemographic::findOrFail($id);
            $mg->name = $request->name;
            if($mg->save()) {
                return $mg;
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>'error', 'message'=>$e->getMessage()], 422);
        }
    }

    public function delete(Request $request, $id){
        try {
            $mg = MangaDemographic::findOrFail($id);
            if($mg->delete()){
                return response()->json(['status'=>'success', 'message'=>'Successfully Deleted Demographic']);
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>'error', 'message'=>$e->getMessage()], 422);
        }
    }
}
