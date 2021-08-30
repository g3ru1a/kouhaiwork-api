<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
    	return [
    	    'name' => $this->faker->sentence,
            'owner_id' => function () {
                return User::first()->id;
            },
    	];
    }
}
