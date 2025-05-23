<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chapter extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['volume', 'number', 'name'];

    // protected static function booted()
    // {
    //     static::deleting(function ($chapter) {
    //         foreach ($chapter->pages as $page) {
    //             $page->delete();
    //         }
    //     });
    // }

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
