<?php

namespace App\Http\Controllers;

use App\Models\Media;
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
}
