<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manga extends Model
{
    use HasFactory;

    protected $casts = [
        'alternative_titles' => 'array'
    ];

    protected $guarded = [];

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
        return $this->hasMany(Chapter::class)->orderBy('number', 'asc');
    }
}
