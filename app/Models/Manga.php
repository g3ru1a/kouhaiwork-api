<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;

class Manga extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'alternative_titles' => 'array'
    ];

    protected $guarded = [];

    protected $hidden = [
        'created_at', 'updated_at',
    ];

    public static function groups($manga)
    {
        $grps = [];
        foreach ($manga->chapters as $chap) {
            if ($chap->groups) {
                foreach ($chap->groups as $g) {
                    if (!in_array($g->name, $grps)) {
                        array_push($grps, $g->name);
                    }
                }
            }
        }
        return $grps;
    }

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
