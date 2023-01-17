<?php

namespace Database\Factories;

use App\Models\FederalEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ZipCode>
 */
class ZipCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'zip_code' => fake()->unique()->randomNumber(4),
            'locality' => strtoupper(fake()->word())
        ];
    }
}
