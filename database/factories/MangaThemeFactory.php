<?php

namespace Database\Factories;

use App\Models\MangaTheme;
use Illuminate\Database\Eloquent\Factories\Factory;

class MangaThemeFactory extends Factory
{
    protected $model = MangaTheme::class;

    public function definition(): array
    {
    	return [
    	    'name' => $this->faker->word
    	];
    }
}
