<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FederalEntity>
 */
class FederalEntityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'key' => fake()->unique()->randomNumber(3),
            'name' => strtoupper(fake()->word()),
            'code' => fake()->word()
        ];
    }
}
