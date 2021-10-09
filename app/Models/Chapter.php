<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chapter extends Model
{
    use SoftDeletes;

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($chapter) { // before delete() method call this
            $chapter->pages()->delete();
        });
    }

    protected $fillable = ['volume', 'number', 'name'];

    public function manga(){
        return $this->belongsTo(Manga::class);
    }

    public function pages(){
        return $this->hasMany(Page::class)->orderBy('next_id', 'desc');
    }

    public function groups(){
        return $this->belongsToMany(Group::class);
    }
}
