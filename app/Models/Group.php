<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'owner_id'];

    public function mangas(){
        return $this->belongsToMany(Manga::class);
    }

    public function chapters(){
        return $this->belongsToMany(Chapter::class);
    }

    public function members(){
        return $this->belongsToMany(User::class);
    }

    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function banner()
    {
        return $this->morphOne(Media::class, 'imageable');
    }
}
