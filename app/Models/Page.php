<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $with = ['media'];

    public function media() {
        return $this->morphOne(Media::class, 'imageable');
    }
}
