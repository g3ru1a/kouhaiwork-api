<?php

namespace App\Http\Controllers;

use App\Http\Requests\MangaRequest;
use App\Services\MangaService;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    public function index(){
        return MangaService::hasChapters()->withEverything()->latest()->toResource();
    }

    public function allWithChapters(){
        return MangaService::hasChapters()->withCover()->all()->toResource();
    }

    public function allNotDeleted()
    {
        return MangaService::notDeleted()->withCover()->all()->toResource();
    }

    public function getNotDeletedWithEverything($id)
    {
        return MangaService::notDeleted()->withEverything()->find($id)->toResource();
    }

    public function getHasChaptersWithEverything($id)
    {
        return MangaService::hasChapters()->withEverything()->find($id)->toResource();
    }

    public function store(MangaRequest $request)
    {
        return MangaService::make($request)->attachCover()->attachRelations()->result()->toResource();
    }

    public function update(MangaRequest $request, $id)
    {
        return MangaService::select($id)->update($request)->attachCover()->updateRelations()->result()->toResource();
    }

    public function delete(Request $request, $id)
    {
        return MangaService::select($id)->delete();
    }

    /**Probably useless, test and remove */
    // public function allSince($chapter_id){
    //     $ch = Chapter::find($chapter_id);
    //     if(!$ch) return response()->json(['message'=>'Chapter not found.'], 422);
    //     $chs = Chapter::with('manga')->groupBy('manga_id')->where('uploaded', true)->where('created_at', '>=', $ch->updated_at)->get();
    //     $manga = [];
    //     foreach ($chs as $chap) {
    //         if ($chap->manga) {
    //             array_push($manga, $chap->manga);
    //         }
    //     }
    //     return MangaWeekResource::collection($manga);
    // }

    /**Move to chapter as 'this weeks/latest 8-10 chapters' */
    // public function week(){
    //     $chapters = Chapter::with('manga', 'manga.cover', 'manga.chapters')->where('uploaded', true)->groupBy('manga_id')->orderBy('updated_at', 'asc')->take(8)->get();
    //     $manga = [];
    //     foreach ($chapters as $chap) {
    //         if($chap->manga){
    //             array_push($manga, $chap->manga);
    //         }
    //     }
    //     return MangaWeekResource::collection($manga);
    // }

    /**Move to chapter logic as 'latest chapter'*/
    // public function latest(){
    //     $lc = Chapter::whereNull('deleted_at')->where('uploaded', true)->orderBy('updated_at', 'desc')->get()->first();
    //     if($lc) {
    //         $manga = Manga::with($this->manga_opt)->whereHas('chapters')->whereNull('deleted_at')->find($lc->manga_id);
    //         // return $manga;
    //         return MangaLatestResource::make($manga);
    //     }else return response()->json(['error'=>'No chapter found.']);
    // }

    /**Move to chapter logic, obviously... */
    // public function chapters($id) {
    //     return Manga::with(['chapters.pages'])->findOrFail($id)->chapters;
    // }
}
