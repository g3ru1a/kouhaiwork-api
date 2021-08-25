<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function upload(Request $request){
        $pages = $request->file('pages');
        if($request->hasFile('pages')){
            // foreach($pages as $page){
            //     //
            // }
        }
        return response()->json([count($pages), json_decode($request->input('order'))]);
    }
}
