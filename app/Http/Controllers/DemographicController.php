<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\DemographicRequest;
use App\Http\Resources\DemographicResource;
use App\Models\MangaDemographic;
use Illuminate\Http\Request;

class DemographicController extends Controller
{
    public function index(){
        return DemographicResource::collection(MangaDemographic::all());
    }

    public function store(DemographicRequest $request){
        try {
            $md = MangaDemographic::create([
                'name' => $request->name
            ]);
            return DemographicResource::make($md);
        } catch (\Exception $e) {
            throw $e;
        }
        
    }

    public function update(DemographicRequest $request, $id){
        try {
            $md = MangaDemographic::find($id);
            throw_if($md === null, new ModelNotFoundException('Demographic'));
            $md->name = $request->name;
            if($md->save()) {
                return DemographicResource::make($md);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function delete(Request $request, $id){
        try {
            $md = MangaDemographic::find($id);
            throw_if($md === null, new ModelNotFoundException('Demographic'));
            if($md->delete()){
                return response()->json(['data'=>['message'=>'Successfully Deleted Demographic']]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
