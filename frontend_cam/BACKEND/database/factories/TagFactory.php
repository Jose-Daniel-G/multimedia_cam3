<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->word(20);
        return [
            'name' => $name,
            'slug'=> Str::slug($name),
            'color'=> $this->faker->randomElement(['red','yellow','green','indigo','purple','pink']),
        ];
    }
}
