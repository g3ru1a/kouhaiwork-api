<?php

namespace Database\Factories;

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
            'alternative_titles' => $this->faker->sentence,
            'status' => 'ongoing',
    	    'cover_url' => 'https://uploads.mangadex.org/covers/e78a489b-6632-4d61-b00b-5206f5b8b22b/1ace3d95-a780-41fd-817f-16000ac1ddd8.jpg'
    	];
    }
}
