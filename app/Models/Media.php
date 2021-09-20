<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    // protected static function booted()
    // {
    //     static::deleting(function ($media) {
    //         // $file = base64_decode($media->filename);
    //         // $url = str_replace('https://s3.eu-west-1.amazonaws.com', '', $media->url);
    //         // $media->url = 'cringe';
    //         // $media->save();
    //         // Storage::disk('s3')->delete($file);
    //     });
    // }

    public function imageable()
    {
        return $this->morphTo();
    }
}
