<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
    	return [
    	    'filename' => $this->faker->sentence,
            'url' => 'https://picsum.photos/400/600'
    	];
    }
}
