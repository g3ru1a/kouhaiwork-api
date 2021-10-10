<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manga extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'alternative_titles' => 'array'
    ];

    protected $guarded = [];

    // protected static function booted()
    // {
    //     static::deleting(function ($manga) {
    //         foreach ($manga->chapters as $chapter) {
    //             $chapter->delete();
    //         }
    //         $manga->cover->delete();
    //     });
    // }

    public function cover(){
        return $this->morphOne(Media::class, 'imageable');
    }

    public function genres(){
        return $this->belongsToMany(MangaGenre::class);
    }

    public function themes(){
        return $this->belongsToMany(MangaTheme::class);
    }

    public function demographics(){
        return $this->belongsToMany(MangaDemographic::class);
    }

    public function groups(){
        return $this->belongsToMany(Group::class);
    }

    public function authors(){
        return $this->belongsToMany(Author::class);
    }

    public function artists(){
        return $this->belongsToMany(Artist::class);
    }

    public function chapters(){
        return $this->hasMany(Chapter::class)->where('uploaded', true)->orderBy('number', 'asc');
    }
}
