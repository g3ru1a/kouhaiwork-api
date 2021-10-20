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
    public function detachS3(){
        $media = Media::all();
        foreach($media as $m){
            $filename = explode('/', $m->url);
            $filename = end($filename);
            $ext = explode('.', $m->url);
            $ext = end($ext);
            $path = str_replace('https://s3.eu-west-1.amazonaws.com/uploads.kouhai.work/', '', $m->url);
            $m->url = $path;
            $m->filename = $filename;
            $m->save();
        }
    }

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
        if(!is_dir($dirPath)) return;
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
