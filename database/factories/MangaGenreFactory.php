<?php

namespace Database\Factories;

use App\Models\MangaGenre;
use Illuminate\Database\Eloquent\Factories\Factory;

class MangaGenreFactory extends Factory
{
    protected $model = MangaGenre::class;

    public function definition(): array
    {
    	return [
    	    'name' => $this->faker->word
    	];
    }
}
