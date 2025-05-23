<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MangaGenre extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
    
    public function mangas(){
        return $this->belongsToMany(Manga::class);
    }
}
