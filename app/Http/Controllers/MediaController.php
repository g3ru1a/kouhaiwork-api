<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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

    public static function upload($fileFromRequest, $folder){
        $contents = file_get_contents($fileFromRequest);
        $name = self::randomFileName().'.'.$fileFromRequest->getClientOriginalExtension();
        $path = $folder . '/' . $name;
        Storage::disk('public')->put($path, $contents);
        return Media::create([
            'filename' => $name,
            'url' => $path,
       ]);
    }

    public static function uploadPage($file, $next_id, $folder, $first = false){
        $media = MediaController::upload($file, $folder);
        $page = new Page();
        $page->next_id = $next_id;
        $page->first = $first;
        $page->save();
        $page->refresh();
        $page->media()->save($media);
        return $page;
    }

    public static function deleteDir($dirPath)
    {
        $it = new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator(
                $it,
                RecursiveIteratorIterator::CHILD_FIRST
            );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dirPath);
    }

    private static function randomFileName(){
        $str = self::generateRandomString(10);
        $str = str_replace('==','', base64_encode($str)).sha1(time());
        return $str;
    }

    private static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
