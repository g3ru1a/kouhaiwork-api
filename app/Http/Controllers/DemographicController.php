<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\DemographicRequest;
use App\Http\Resources\DemographicResource;
use App\Models\MangaDemographic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DemographicController extends Controller
{
    private $cacheKey = 'manga-demographics';
    public function index()
    {
        if (Cache::has($this->cacheKey)) {
            return response()->json(json_decode(Cache::get($this->cacheKey)));
        } else {
            $data = MangaDemographic::all();
            $col = DemographicResource::collection($data);
            Cache::put($this->cacheKey, json_encode($col->response()->getData()), 60 * 60 * 24);
            return $col;
        }
    }

    public function store(DemographicRequest $request){
        try {
            $md = MangaDemographic::create([
                'name' => $request->name
            ]);
            Cache::forget($this->cacheKey);
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
                Cache::forget($this->cacheKey);
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
            if($md->delete()) {
                Cache::forget($this->cacheKey);
                return response()->json(['data'=>['message'=>'Successfully Deleted Demographic']]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
