<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    // public function test(Request $request){
    //     $path = $request->file('image')->store('images', 's3');
    //     Storage::disk('s3')->setVisibility($path, 'public');
    //     $media = Media::create([
    //         'filename' => basename($path),
    //         'url' => Storage::disk('s3')->url($path)
    //     ]);

    //     return response()->json(['media'=>$media]);
    // }

    public static function upload(Request $request, $field, $folder){
        $path = $request->file($field)->store($folder, 's3');
        Storage::disk('s3')->setVisibility($path, 'public');
        $media = Media::create([
            'filename' => basename($path),
            'url' => Storage::disk('s3')->url($path)
        ]);
        return $media;
    }

    public static function uploadFromFile($file, $folder)
    {
        $path = $file->store($folder, 's3');
        Storage::disk('s3')->setVisibility($path, 'public');
        $media = new Media([
            'filename' => basename($path),
            'url' => Storage::disk('s3')->url($path)
        ]);
        return $media;
    }

    public static function uploadPage($file, $next_id, $folder, $first = false){
        $media = MediaController::uploadFromFile($file, $folder);
        $page = new Page();
        $page->next_id = $next_id;
        $page->first = $first;
        $page->save();
        $page->refresh();
        $page->media()->save($media);
        return $page;
    }
}
