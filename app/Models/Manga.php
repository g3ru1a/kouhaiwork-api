<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
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

    protected $fillable = [
        'title', 'synopsis', 'status', 'origin', 'alternative_title', 'created_by'
    ];

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

    public static function getGroups($id){
        $m = Manga::findOrFail($id);
        $chapters = $m->chapters;
        $groups = new Collection();
        foreach($chapters as $c){
            foreach($c->groups as $g){
                $groups->add($g);
            }
        }
        $groups = $groups->unique();
        return $groups;
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

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
