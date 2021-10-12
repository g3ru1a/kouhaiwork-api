<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $with = ['media'];

    // protected static function booted()
    // {
    //     static::deleting(function ($page) {
    //         foreach ($page->media as $media) {
    //             $media->delete();
    //         }
    //     });
    // }

    public function media() {
        return $this->morphOne(Media::class, 'imageable');
    }
}
