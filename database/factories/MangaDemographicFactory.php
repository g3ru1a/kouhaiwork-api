<?php

namespace Database\Factories;

use App\Models\MangaDemographic;
use Illuminate\Database\Eloquent\Factories\Factory;

class MangaDemographicFactory extends Factory
{
    protected $model = MangaDemographic::class;

    public function definition(): array
    {
    	return [
    	    'name' => $this->faker->word
    	];
    }
}
