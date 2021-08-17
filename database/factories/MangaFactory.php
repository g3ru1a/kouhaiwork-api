<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Manga;
use Illuminate\Database\Eloquent\Factories\Factory;

class MangaFactory extends Factory
{
    protected $model = Manga::class;

    public function definition(): array
    {
    	return [
            'title' => $this->faker->sentence,
            'synopsis' => $this->faker->paragraph,
            'alternative_titles' => [$this->faker->sentence,$this->faker->sentence],
            'status' => 'ongoing',
    	];
    }
}
