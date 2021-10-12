<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChapterFactory extends Factory
{
    protected $model = Chapter::class;

    public function definition(): array
    {
    	return [
    	    'number' => $this->faker->randomFloat(1, 1, 60),
            'uploaded' => true,
    	];
    }
}
