<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => $this->faker->randomElement(['HINO', 'MITSUBISHI', 'ISUZU', 'TATA', 'VOLVO']),
            'no_pol'        => 'BM ' . $this->faker->randomNumber(4, true) . ' ' . mb_strtoupper(substr($this->faker->words(2, true), 0, '2')),
            'year'          => rand(2018, 2023),
            'description'   => $this->faker->words(6, true),
        ];
    }
}
