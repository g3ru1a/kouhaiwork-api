<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manga extends Model
{
    use HasFactory;

    public function genres(){
        return $this->belongsToMany(MangaGenre::class);
    }

    public function themes(){
        return $this->belongsToMany(MangaTheme::class);
    }

    public function demographics(){
        return $this->belongsToMany(MangaDemographic::class);
    }
}
